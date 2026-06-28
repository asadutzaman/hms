<?php

namespace App\Services\Opd;

use App\Exceptions\ApiException;
use App\Models\OpdDiagnosis;
use App\Models\OpdVisit;
use App\Repositories\OpdDiagnosisRepository;
use App\Validators\OpdDiagnosisValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpdDiagnosisService
{
    protected $repo;

    public function __construct(OpdDiagnosisRepository $repo)
    {
        $this->repo = $repo;
    }

    public function create(int $visitId, array $data, int $actorId): OpdDiagnosis
    {
        $data['opd_visit_id'] = $visitId;
        $this->validateOrThrow($data, 'POST');

        return DB::transaction(function () use ($visitId, $data, $actorId) {
            $visit = OpdVisit::query()->lockForUpdate()->find($visitId);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            // Enforce single primary per visit
            if (!empty($data['is_primary'])) {
                $this->repo->newQuery()
                    ->where('opd_visit_id', $visitId)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            return $this->repo->create(array_merge($data, [
                'patient_id'   => $visit->patient_id,
                'sequence'     => $data['sequence'] ?? ($this->nextSequence($visitId)),
                'recorded_by'  => $actorId,
                'recorded_at'  => $data['recorded_at'] ?? now(),
                'created_by'   => $actorId,
                'updated_by'   => $actorId,
            ]));
        });
    }

    public function update(int $id, array $data, int $actorId): OpdDiagnosis
    {
        $this->validateOrThrow($data, 'PUT');

        return DB::transaction(function () use ($id, $data, $actorId) {
            $dx = $this->repo->find($id);
            if (!$dx) {
                throw new ApiException('Diagnosis not found.', 404);
            }

            if (!empty($data['is_primary'])) {
                $this->repo->newQuery()
                    ->where('opd_visit_id', $dx->opd_visit_id)
                    ->where('id', '!=', $id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $this->repo->update($id, array_merge($data, [
                'updated_by' => $actorId,
            ]));

            return $this->repo->find($id);
        });
    }

    public function listForVisit(int $visitId)
    {
        return $this->repo->newQuery()
            ->where('opd_visit_id', $visitId)
            ->orderBy('sequence')
            ->get();
    }

    public function find(int $id): ?OpdDiagnosis
    {
        return $this->repo->find($id);
    }

    public function delete(int $id, int $actorId): bool
    {
        $dx = $this->repo->find($id);
        if (!$dx) {
            throw new ApiException('Diagnosis not found.', 404);
        }
        return $this->repo->delete($id);
    }

    protected function nextSequence(int $visitId): int
    {
        $max = (int) $this->repo->newQuery()
            ->where('opd_visit_id', $visitId)
            ->max('sequence');
        return $max + 1;
    }

    protected function validateOrThrow(array $data, string $method): void
    {
        $v = app(OpdDiagnosisValidator::class);
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