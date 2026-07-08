<?php

namespace App\Services\BloodBank;

use App\Exceptions\ApiException;
use App\Models\BloodCrossMatch;
use App\Models\BloodUnit;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

/**
 * F-11-03 Cross Matching & Transfusion Record. A real cross-match is a
 * physical serological test (saline/Coombs) — this service doesn't
 * simulate the lab test itself, but DOES enforce ABO/Rh compatibility as a
 * hard safety gate: a technician cannot record 'compatible' for an ABO/Rh
 * combination that is never compatible (e.g. A+ recipient with a B- unit),
 * regardless of what the physical test result was, since that combination
 * points to a sample-handling or unit-selection error, not a valid result.
 */
class BloodCrossMatchService
{
    /** donor blood group => compatible recipient blood groups. */
    protected const COMPATIBILITY = [
        'O-'  => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
        'O+'  => ['O+', 'A+', 'B+', 'AB+'],
        'A-'  => ['A-', 'A+', 'AB-', 'AB+'],
        'A+'  => ['A+', 'AB+'],
        'B-'  => ['B-', 'B+', 'AB-', 'AB+'],
        'B+'  => ['B+', 'AB+'],
        'AB-' => ['AB-', 'AB+'],
        'AB+' => ['AB+'],
    ];

    public function isAboRhCompatible(string $donorGroup, string $recipientGroup): bool
    {
        return in_array($recipientGroup, self::COMPATIBILITY[$donorGroup] ?? [], true);
    }

    public function performCrossMatch(array $data, int $actorId): BloodCrossMatch
    {
        return DB::transaction(function () use ($data, $actorId) {
            $unit = BloodUnit::query()->findOrFail($data['blood_unit_id']);
            $patient = Patient::query()->findOrFail($data['patient_id']);

            if ($unit->unit_status !== 'available' && $unit->unit_status !== 'reserved') {
                throw new ApiException("Unit is not available for cross-matching (current status: {$unit->unit_status}).", 422);
            }

            $recipientGroup = $data['patient_blood_group'] ?? $patient->blood_group;
            $requestedResult = $data['cross_match_result'] ?? 'pending';
            $aboRhCompatible = $recipientGroup ? $this->isAboRhCompatible($unit->blood_group, $recipientGroup) : null;

            if ($requestedResult === 'compatible' && $aboRhCompatible === false) {
                throw new ApiException(
                    "ABO/Rh incompatible: unit is {$unit->blood_group}, patient is {$recipientGroup}. Cannot record as compatible.",
                    422
                );
            }

            $crossMatch = BloodCrossMatch::query()->create([
                'patient_id'            => $patient->id,
                'blood_unit_id'         => $unit->id,
                'patient_blood_group'   => $recipientGroup,
                'cross_match_result'    => $requestedResult,
                'method'                => $data['method'] ?? null,
                'performed_by'          => $actorId,
                'performed_at'          => now(),
                'notes'                 => $data['notes'] ?? null,
                'created_by'            => $actorId,
            ]);

            if ($requestedResult === 'compatible') {
                $unit->unit_status = 'reserved';
                $unit->updated_by = $actorId;
                $unit->save();
            }

            return $crossMatch->fresh(['patient', 'bloodUnit']);
        });
    }
}
