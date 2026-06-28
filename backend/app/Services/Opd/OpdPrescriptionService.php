<?php

namespace App\Services\Opd;

use App\Exceptions\ApiException;
use App\Models\OpdPrescription;
use App\Models\OpdPrescriptionItem;
use App\Models\OpdVisit;
use App\Repositories\OpdPrescriptionItemRepository;
use App\Repositories\OpdPrescriptionRepository;
use App\Validators\OpdPrescriptionValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpdPrescriptionService
{
    protected $rxRepo;
    protected $itemRepo;

    public function __construct(
        OpdPrescriptionRepository $rxRepo,
        OpdPrescriptionItemRepository $itemRepo,
    ) {
        $this->rxRepo = $rxRepo;
        $this->itemRepo = $itemRepo;
    }

    /**
     * Create a prescription with nested items in a single transaction.
     *
     * @param int   $visitId
     * @param array $data  validated input; must contain items[].
     * @param int   $actorId
     */
    public function create(int $visitId, array $data, int $actorId): OpdPrescription
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new ApiException('At least one prescription item is required.', 422);
        }

        $data['opd_visit_id'] = $visitId;
        $this->validateOrThrow($data, 'POST');

        return DB::transaction(function () use ($visitId, $data, $actorId) {
            $visit = OpdVisit::query()->lockForUpdate()->find($visitId);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            $rx = $this->rxRepo->create([
                'opd_visit_id'   => $visitId,
                'patient_id'     => $visit->patient_id,
                'prescribed_by'  => $actorId,
                'prescribed_at'  => $data['prescribed_at'] ?? now(),
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'is_printed'     => $data['is_printed'] ?? false,
                'printed_at'     => $data['printed_at'] ?? null,
                'advice'         => $data['advice']         ?? null,
                'notes'          => $data['notes']          ?? null,
                'created_by'     => $actorId,
                'updated_by'     => $actorId,
            ]);

            foreach (array_values($data['items']) as $i => $row) {
                $this->itemRepo->create(array_merge([
                    'opd_prescription_id' => $rx->id,
                    'sequence'            => $i + 1,
                    'is_prn'              => $row['is_prn'] ?? false,
                    'amount'              => $row['amount'] ?? 0,
                    'created_by'          => $actorId,
                    'updated_by'          => $actorId,
                ], $row));
            }

            return $rx->fresh(['items']);
        });
    }

    public function update(int $rxId, array $data, int $actorId): OpdPrescription
    {
        $this->validateOrThrow($data, 'PUT');

        return DB::transaction(function () use ($rxId, $data, $actorId) {
            $rx = $this->rxRepo->find($rxId);
            if (!$rx) {
                throw new ApiException('Prescription not found.', 404);
            }

            $this->rxRepo->update($rxId, array_merge($data, [
                'updated_by' => $actorId,
            ]));

            return $this->rxRepo->find($rxId);
        });
    }

    /**
     * Replace the items on an existing prescription atomically.
     */
    public function replaceItems(int $rxId, array $items, int $actorId): OpdPrescription
    {
        if (empty($items) || !is_array($items)) {
            throw new ApiException('At least one item is required.', 422);
        }

        return DB::transaction(function () use ($rxId, $items, $actorId) {
            $rx = $this->rxRepo->find($rxId);
            if (!$rx) {
                throw new ApiException('Prescription not found.', 404);
            }

            // Mark old items inactive (soft-delete via status_flag) and create new ones
            OpdPrescriptionItem::query()
                ->where('opd_prescription_id', $rxId)
                ->update([
                    'status_flag' => 0,
                    'updated_by'  => $actorId,
                    'updated_at'  => now(),
                ]);

            foreach (array_values($items) as $i => $row) {
                $this->itemRepo->create(array_merge([
                    'opd_prescription_id' => $rxId,
                    'sequence'            => $i + 1,
                    'is_prn'              => $row['is_prn'] ?? false,
                    'amount'              => $row['amount'] ?? 0,
                    'created_by'          => $actorId,
                    'updated_by'          => $actorId,
                ], $row));
            }

            return $rx->fresh(['items']);
        });
    }

    /**
     * Mark prescription as printed (sets is_printed=true and printed_at).
     */
    public function markPrinted(int $rxId, int $actorId): OpdPrescription
    {
        $rx = $this->rxRepo->find($rxId);
        if (!$rx) {
            throw new ApiException('Prescription not found.', 404);
        }

        $this->rxRepo->update($rxId, [
            'is_printed' => true,
            'printed_at' => now(),
            'updated_by' => $actorId,
        ]);

        return $this->rxRepo->find($rxId);
    }

    public function listForVisit(int $visitId)
    {
        return $this->rxRepo->newQuery()
            ->with('items')
            ->where('opd_visit_id', $visitId)
            ->orderByDesc('prescribed_at')
            ->get();
    }

    public function find(int $id): ?OpdPrescription
    {
        return $this->rxRepo->find($id);
    }

    public function delete(int $id, int $actorId): bool
    {
        $rx = $this->rxRepo->find($id);
        if (!$rx) {
            throw new ApiException('Prescription not found.', 404);
        }
        // Cascade soft-delete items
        OpdPrescriptionItem::query()
            ->where('opd_prescription_id', $id)
            ->update([
                'status_flag' => 0,
                'updated_by'  => $actorId,
                'updated_at'  => now(),
            ]);
        return $this->rxRepo->delete($id);
    }

    protected function validateOrThrow(array $data, string $method): void
    {
        $v = app(OpdPrescriptionValidator::class);
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