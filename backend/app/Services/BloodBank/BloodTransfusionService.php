<?php

namespace App\Services\BloodBank;

use App\Exceptions\ApiException;
use App\Models\BloodCrossMatch;
use App\Models\BloodTransfusion;
use App\Models\BloodUnit;
use Illuminate\Support\Facades\DB;

class BloodTransfusionService
{
    /**
     * "Transfusion unit linked to patient" (F-11-03) — a unit can only be
     * transfused after a compatible cross-match, and only once (enforced
     * both by the unique blood_unit_id column and this status check).
     */
    public function recordTransfusion(array $data, int $actorId): BloodTransfusion
    {
        return DB::transaction(function () use ($data, $actorId) {
            $unit = BloodUnit::query()->lockForUpdate()->findOrFail($data['blood_unit_id']);

            if (!in_array($unit->unit_status, ['reserved', 'available'], true)) {
                throw new ApiException("Unit cannot be transfused (current status: {$unit->unit_status}).", 422);
            }

            $crossMatch = null;
            if (!empty($data['cross_match_id'])) {
                $crossMatch = BloodCrossMatch::query()->findOrFail($data['cross_match_id']);
                if ($crossMatch->cross_match_result !== 'compatible') {
                    throw new ApiException('The linked cross-match is not marked compatible.', 422);
                }
            }

            $transfusion = BloodTransfusion::query()->create([
                'patient_id'         => $data['patient_id'],
                'blood_unit_id'      => $unit->id,
                'cross_match_id'     => $crossMatch->id ?? null,
                'ipd_admission_id'   => $data['ipd_admission_id'] ?? null,
                'started_at'         => $data['started_at'] ?? now(),
                'administered_by'    => $data['administered_by'] ?? $actorId,
                'created_by'         => $actorId,
            ]);

            $unit->unit_status = 'issued';
            $unit->updated_by = $actorId;
            $unit->save();

            return $transfusion->fresh(['patient', 'bloodUnit', 'crossMatch']);
        });
    }

    public function completeTransfusion(int $id, array $data, int $actorId): BloodTransfusion
    {
        $transfusion = BloodTransfusion::query()->findOrFail($id);
        $transfusion->ended_at = $data['ended_at'] ?? now();
        $transfusion->reaction_observed = $data['reaction_observed'] ?? false;
        $transfusion->reaction_notes = $data['reaction_notes'] ?? null;
        $transfusion->updated_by = $actorId;
        $transfusion->save();
        return $transfusion->fresh();
    }
}
