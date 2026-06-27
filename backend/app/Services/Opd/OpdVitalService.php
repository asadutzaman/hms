<?php

namespace App\Services\Opd;

use App\Enums\OpdVisitActionEnum;
use App\Exceptions\ApiException;
use App\Models\OpdVital;
use App\Models\OpdVisit;
use App\Repositories\OpdVisitRepository;
use App\Repositories\OpdVitalRepository;
use App\Validators\OpdVitalValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpdVitalService
{
    protected $vitalRepo;
    protected $visitRepo;

    public function __construct(
        OpdVitalRepository $vitalRepo,
        OpdVisitRepository $visitRepo,
    ) {
        $this->vitalRepo = $vitalRepo;
        $this->visitRepo = $visitRepo;
    }

    /**
     * Record vitals for a visit. Triggers WAITING → VITALS_TAKEN auto-transition
     * on the parent visit if it is currently WAITING.
     */
    public function record(int $visitId, array $data, int $actorId): OpdVital
    {
        $data['opd_visit_id'] = $visitId;
        $this->validateOrThrow($data, 'POST');

        return DB::transaction(function () use ($visitId, $data, $actorId) {
            $visit = OpdVisit::query()->lockForUpdate()->find($visitId);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            // Compute BMI if weight + height are given and BMI not passed
            if (
                !isset($data['bmi']) &&
                !empty($data['weight_kg']) &&
                !empty($data['height_cm']) &&
                (float) $data['height_cm'] > 0
            ) {
                $heightM = (float) $data['height_cm'] / 100.0;
                $data['bmi'] = round(((float) $data['weight_kg']) / ($heightM * $heightM), 2);
            }

            $vital = $this->vitalRepo->create(array_merge($data, [
                'patient_id'   => $visit->patient_id,
                'recorded_by'  => $actorId,
                'recorded_at'  => $data['recorded_at'] ?? now(),
                'created_by'   => $actorId,
                'updated_by'   => $actorId,
            ]));

            // Auto-transition: WAITING → VITALS_TAKEN
            if ($visit->status === 'waiting') {
                $this->visitRepo->transitionStatus(
                    $visit->id,
                    'vitals_taken',
                    $actorId,
                    'Vitals recorded',
                    ['vital_id' => $vital->id],
                    OpdVisitActionEnum::STATUS_CHANGE,
                );
            } else {
                // Audit only — no transition
                $this->visitRepo->logAudit(
                    $visit,
                    OpdVisitActionEnum::UPDATE,
                    null,
                    null,
                    $actorId,
                    'Vitals updated',
                    ['vital_id' => $vital->id],
                );
            }

            return $vital->fresh();
        });
    }

    public function update(int $vitalId, array $data, int $actorId): OpdVital
    {
        $this->validateOrThrow($data, 'PUT');

        return DB::transaction(function () use ($vitalId, $data, $actorId) {
            $vital = $this->vitalRepo->find($vitalId);
            if (!$vital) {
                throw new ApiException('Vitals record not found.', 404);
            }

            $this->vitalRepo->update($vitalId, array_merge($data, [
                'updated_by' => $actorId,
            ]));

            return $this->vitalRepo->find($vitalId);
        });
    }

    public function listForVisit(int $visitId)
    {
        return $this->vitalRepo->newQuery()
            ->where('opd_visit_id', $visitId)
            ->orderByDesc('recorded_at')
            ->get();
    }

    public function find(int $id): ?OpdVital
    {
        return $this->vitalRepo->find($id);
    }

    public function delete(int $id, int $actorId): bool
    {
        $vital = $this->vitalRepo->find($id);
        if (!$vital) {
            throw new ApiException('Vitals record not found.', 404);
        }
        return $this->vitalRepo->delete($id);
    }

    protected function validateOrThrow(array $data, string $method): void
    {
        $v = app(OpdVitalValidator::class);
        $rules = $v->rules();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ApiException(
                'Validation failed: ' . implode('; ', $validator->errors()->all()),
                422,
                $validator->errors()->toArray(),
            );
        }
    }
}