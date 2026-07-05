<?php

namespace App\Services\Opd;

use App\Exceptions\ApiException;
use App\Models\ControlledDrugRegister;
use App\Models\Drug;
use App\Models\OpdPrescription;
use App\Models\OpdPrescriptionItem;
use App\Models\PrescriptionDispense;
use App\Repositories\ItemStockOutHistoryRepository;
use App\Repositories\ItemStockRepository;
use App\Services\SessionService;
use Illuminate\Support\Facades\DB;

class PrescriptionDispenseService
{
    protected $itemStockRepository;
    protected $itemStockOutHistoryRepository;

    public function __construct(
        ItemStockRepository $itemStockRepository,
        ItemStockOutHistoryRepository $itemStockOutHistoryRepository,
    ) {
        $this->itemStockRepository = $itemStockRepository;
        $this->itemStockOutHistoryRepository = $itemStockOutHistoryRepository;
    }

    /**
     * Compute a shortfall list (same shape as
     * RequisitionApprovalController::computeShortfall()) for the requested
     * lines against the given branch's stock, without writing anything.
     */
    public function computeShortfall(int $branchId, array $lines): array
    {
        $shortfall = [];

        foreach ($lines as $line) {
            $prescriptionItem = OpdPrescriptionItem::query()->find($line['opd_prescription_item_id']);
            if (!$prescriptionItem || !$prescriptionItem->drug_id) {
                continue;
            }

            $drug = Drug::query()->with('item')->find($prescriptionItem->drug_id);
            if (!$drug) {
                continue;
            }

            $requestedQty = (float) $line['dispensed_quantity'];
            $availableQty = $this->itemStockRepository->getItemWiseAvailableStockQty($branchId, $drug->item_id);

            if ($availableQty < $requestedQty) {
                $shortfall[] = [
                    'opd_prescription_item_id' => $prescriptionItem->id,
                    'drug_id'                  => $drug->id,
                    'item_code'                => $drug->item->code ?? null,
                    'drug_name'                => $prescriptionItem->drug_name,
                    'requested_qty'            => $requestedQty,
                    'available_qty'            => (float) $availableQty,
                    'shortfall_qty'            => $requestedQty - (float) $availableQty,
                ];
            }
        }

        return $shortfall;
    }

    /**
     * Dispense one or more prescription items from the dispensing branch's
     * stock (FEFO-ordered), recording a dispense header + per-line ledger
     * entries and decrementing item_stocks.balance_quantity — mirrors
     * RequisitionApprovalController::disburseItem()'s stock-out steps.
     *
     * $lines: [['opd_prescription_item_id' => int, 'dispensed_quantity' => float, 'witnessed_by' => ?int], ...]
     */
    public function dispense(int $prescriptionId, array $lines, int $actorId, ?int $branchId = null): PrescriptionDispense
    {
        if (empty($lines)) {
            throw new ApiException('At least one item is required to dispense.', 422);
        }

        $branchId = $branchId ?? (new SessionService())->init()->getUserBranchId();
        if (empty($branchId)) {
            throw new ApiException('No branch is associated with the current session.', 422);
        }

        $shortfall = $this->computeShortfall($branchId, $lines);
        if (!empty($shortfall)) {
            throw new ApiException(
                'Insufficient stock for: ' . implode(', ', array_column($shortfall, 'drug_name')) . '.',
                422,
                ['shortfall' => $shortfall],
            );
        }

        return DB::transaction(function () use ($prescriptionId, $lines, $actorId, $branchId) {
            $prescription = OpdPrescription::query()->lockForUpdate()->find($prescriptionId);
            if (!$prescription) {
                throw new ApiException('Prescription not found.', 404);
            }

            $dispense = PrescriptionDispense::query()->create([
                'organogram_id'        => $prescription->organogram_id,
                'opd_prescription_id'  => $prescriptionId,
                'patient_id'           => $prescription->patient_id,
                'branch_id'            => $branchId,
                'dispensed_by'         => $actorId,
                'dispensed_at'         => now(),
                'dispense_status'      => 'completed',
                'created_by'           => $actorId,
                'updated_by'           => $actorId,
            ]);

            foreach ($lines as $line) {
                $this->dispenseLine($dispense, $line, $branchId, $actorId);
            }

            return $dispense->fresh('items');
        });
    }

    private function dispenseLine(PrescriptionDispense $dispense, array $line, int $branchId, int $actorId): void
    {
        $prescriptionItem = OpdPrescriptionItem::query()->lockForUpdate()->find($line['opd_prescription_item_id']);
        if (!$prescriptionItem || !$prescriptionItem->drug_id) {
            throw new ApiException('Prescription item is not linked to a catalog drug and cannot be dispensed.', 422);
        }

        $drug = Drug::query()->with('item')->find($prescriptionItem->drug_id);
        if (!$drug) {
            throw new ApiException('Drug not found for prescription item.', 404);
        }

        $requestedQty = (float) $line['dispensed_quantity'];
        if ($requestedQty <= 0) {
            throw new ApiException('Dispensed quantity must be greater than zero.', 422);
        }

        if ($drug->is_controlled && empty($line['witnessed_by'])) {
            throw new ApiException("A witness is required to dispense the controlled drug: {$prescriptionItem->drug_name}.", 422);
        }

        $itemWiseStockList = $this->itemStockRepository->getItemWiseStockList('FIFO', $branchId, $drug->item_id, $requestedQty);
        $remainingQty = $requestedQty;
        $lastConsumedExpireDate = null;

        foreach ($itemWiseStockList as $itemStock) {
            if ($remainingQty <= 0) {
                break;
            }

            $consumeQty = min($remainingQty, (float) $itemStock->balance_quantity);
            $lastConsumedExpireDate = $itemStock->expire_date;

            $this->itemStockRepository->update([
                'balance_quantity' => $itemStock->balance_quantity - $consumeQty,
            ], $itemStock->id);

            $this->itemStockOutHistoryRepository->create([
                'branch_id'       => $branchId,
                'item_stock_id'   => $itemStock->id,
                'item_id'         => $itemStock->item_id,
                'quantity'        => $consumeQty,
                'recordable_id'   => $prescriptionItem->id,
                'recordable_type' => OpdPrescriptionItem::class,
                'action_from'     => 'OPD_DISPENSE',
                'remarks'         => 'OPD prescription dispense',
            ]);

            $remainingQty -= $consumeQty;
        }

        $dispense->items()->create([
            'opd_prescription_item_id' => $prescriptionItem->id,
            'drug_id'                  => $drug->id,
            'dispensed_quantity'       => $requestedQty,
            'expire_date'              => $lastConsumedExpireDate,
            'created_by'               => $actorId,
            'updated_by'               => $actorId,
        ]);

        $prescriptionItem->update([
            'dispensed_quantity' => (float) $prescriptionItem->dispensed_quantity + $requestedQty,
        ]);

        if ($drug->is_controlled) {
            ControlledDrugRegister::query()->create([
                'drug_id'                  => $drug->id,
                'patient_id'               => $dispense->patient_id,
                'opd_prescription_item_id' => $prescriptionItem->id,
                'dispensed_quantity'       => $requestedQty,
                'dispensed_by'             => $actorId,
                'witnessed_by'             => $line['witnessed_by'],
                'dispensed_at'             => now(),
                'remarks'                  => $line['remarks'] ?? null,
            ]);
        }
    }
}
