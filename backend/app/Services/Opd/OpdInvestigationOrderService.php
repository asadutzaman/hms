<?php

namespace App\Services\Opd;

use App\Enums\OpdInvestigationOrderStatusEnum;
use App\Exceptions\ApiException;
use App\Models\LabTest;
use App\Models\OpdInvestigationOrder;
use App\Models\OpdInvestigationOrderItem;
use App\Models\OpdVisit;
use App\Repositories\OpdInvestigationOrderItemRepository;
use App\Repositories\OpdInvestigationOrderRepository;
use App\Validators\OpdInvestigationOrderValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpdInvestigationOrderService
{
    protected $orderRepo;
    protected $itemRepo;

    public function __construct(
        OpdInvestigationOrderRepository $orderRepo,
        OpdInvestigationOrderItemRepository $itemRepo,
    ) {
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
    }

    public function create(int $visitId, array $data, int $actorId): OpdInvestigationOrder
    {
        $data['opd_visit_id'] = $visitId;
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new ApiException('At least one investigation item is required.', 422);
        }
        $this->validateOrThrow($data, 'POST');

        return DB::transaction(function () use ($visitId, $data, $actorId) {
            $visit = OpdVisit::query()->lockForUpdate()->find($visitId);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            $order = $this->orderRepo->create([
                'opd_visit_id'       => $visitId,
                'patient_id'         => $visit->patient_id,
                'order_no'           => $data['order_no'] ?? $this->generateOrderNo(),
                'priority'           => $data['priority'] ?? 'routine',
                'status'             => $data['status']   ?? OpdInvestigationOrderStatusEnum::ORDERED,
                'clinical_indication'=> $data['clinical_indication'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'ordered_by'         => $actorId,
                'ordered_at'         => $data['ordered_at'] ?? now(),
                'created_by'         => $actorId,
                'updated_by'         => $actorId,
            ]);

            foreach (array_values($data['items']) as $i => $row) {
                $snapshot = $this->snapshotLabTest($row['lab_test_id'] ?? null, $row);
                $this->itemRepo->create(array_merge($snapshot, [
                    'opd_investigation_order_id' => $order->id,
                    'sequence'                   => $i + 1,
                    'created_by'                 => $actorId,
                    'updated_by'                 => $actorId,
                ], $row));
            }

            return $order->fresh(['items']);
        });
    }

    public function update(int $orderId, array $data, int $actorId): OpdInvestigationOrder
    {
        $this->validateOrThrow($data, 'PUT');

        return DB::transaction(function () use ($orderId, $data, $actorId) {
            $order = $this->orderRepo->find($orderId);
            if (!$order) {
                throw new ApiException('Investigation order not found.', 404);
            }

            if (isset($data['status'])
                && !OpdInvestigationOrderStatusEnum::canTransition((string) $order->status, (string) $data['status'])) {
                throw new ApiException(
                    "Cannot transition status from '{$order->status}' to '{$data['status']}'.",
                    422,
                );
            }

            $this->orderRepo->update($orderId, array_merge($data, [
                'updated_by' => $actorId,
            ]));

            return $this->orderRepo->find($orderId);
        });
    }

    public function transitionStatus(int $orderId, string $toStatus, int $actorId, array $meta = []): OpdInvestigationOrder
    {
        return DB::transaction(function () use ($orderId, $toStatus, $actorId, $meta) {
            $order = $this->orderRepo->find($orderId);
            if (!$order) {
                throw new ApiException('Investigation order not found.', 404);
            }

            if (!in_array($toStatus, OpdInvestigationOrderStatusEnum::getKeys(), true)) {
                throw new ApiException("Invalid status '{$toStatus}'.", 422);
            }

            if (!OpdInvestigationOrderStatusEnum::canTransition((string) $order->status, $toStatus)) {
                throw new ApiException(
                    "Cannot transition from '{$order->status}' to '{$toStatus}'.",
                    422,
                );
            }

            $this->orderRepo->update($orderId, [
                'status'     => $toStatus,
                'updated_by' => $actorId,
            ]);

            return $this->orderRepo->find($orderId);
        });
    }

    public function listForVisit(int $visitId)
    {
        return $this->orderRepo->newQuery()
            ->with('items')
            ->where('opd_visit_id', $visitId)
            ->orderByDesc('ordered_at')
            ->get();
    }

    public function find(int $id): ?OpdInvestigationOrder
    {
        return $this->orderRepo->find($id);
    }

    public function delete(int $id, int $actorId): bool
    {
        $order = $this->orderRepo->find($id);
        if (!$order) {
            throw new ApiException('Investigation order not found.', 404);
        }
        if (!in_array($order->status, ['ordered', 'cancelled'], true)) {
            throw new ApiException(
                "Only ordered or cancelled investigations can be deleted (current: {$order->status}).",
                422,
            );
        }
        return $this->orderRepo->delete($id);
    }

    protected function snapshotLabTest(?int $labTestId, array $row): array
    {
        if (!$labTestId) {
            return [];
        }
        $test = LabTest::query()->find($labTestId);
        if (!$test) {
            return [];
        }
        return [
            'test_name' => $row['test_name'] ?? $test->name,
            'test_code' => $row['test_code'] ?? $test->code,
            'unit_price'=> $row['unit_price'] ?? (float) ($test->price ?? 0),
            'amount'    => $row['amount']    ?? (float) ($test->price ?? 0),
        ];
    }

    protected function generateOrderNo(): string
    {
        return 'INV-' . now()->format('Ymd-His') . '-' . random_int(100, 999);
    }

    protected function validateOrThrow(array $data, string $method): void
    {
        $v = app(OpdInvestigationOrderValidator::class);
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