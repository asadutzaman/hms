<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\GoodsReceiveNote;
use App\Models\GoodsReceiveNoteItem;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Logistic;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RateContract;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Shelve;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use App\Models\VendorQuote;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Pharmacy/procurement volume: requisitions -> purchase orders -> GRNs
 * (with item_stocks balance entries) plus stock adjustments/transfers and
 * vendor-quote/rate-contract comparison data, so the inventory dashboards
 * (stock levels, PO pipeline, requisition backlog) have something to show.
 *
 * Bypasses the requisition->PO->GRN handoff services (forceCreate direct)
 * since this is demo volume, not a business-rule test; still writes an
 * item_stocks row per GRN line so on-hand balances stay consistent.
 *
 * Idempotent: requisitions/POs/GRNs keyed by their *_number columns.
 */
class InventoryDemoSeeder extends Seeder
{
    private array $itemIds;
    private array $supplierIds;
    private int $branchId;
    private int $shelveId;
    private int $logisticId;
    private int $actorId = 1;

    public function run(): void
    {
        $this->command->info('[InventoryDemoSeeder] Starting ...');

        $this->itemIds     = Item::query()->pluck('id')->all();
        $this->supplierIds = Supplier::query()->pluck('id')->all();
        $this->branchId    = Branch::query()->where('name', 'Central Pharmacy Warehouse')->value('id') ?? Branch::query()->value('id');
        $this->shelveId     = Shelve::query()->value('id');
        $this->logisticId   = Logistic::query()->value('id') ?? 1;

        if (empty($this->itemIds) || empty($this->supplierIds)) {
            $this->command->warn('[InventoryDemoSeeder] No items/suppliers found; run MasterDataDemoSeeder first. Skipping.');
            return;
        }

        $this->seedRequisitionsAndPurchaseOrders();
        $this->seedStockAdjustmentsAndTransfers();
        $this->seedVendorQuotesAndRateContracts();

        $this->command->info('[InventoryDemoSeeder] Done.');
    }

    private function seedRequisitionsAndPurchaseOrders(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $reqNo = 'REQ-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $requisition = Requisition::query()->firstOrCreate(
                ['requisition_number' => $reqNo],
                [
                    'branch_id' => $this->branchId, 'logistic_id' => $this->logisticId,
                    'subject' => 'Monthly pharmacy restock', 'description' => 'Demo requisition',
                    'reconcile_status' => 0, 'process_status' => $i <= 4 ? 'APPROVED' : 'SUBMITTED',
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ],
            );

            if ($requisition->wasRecentlyCreated) {
                foreach (array_slice($this->itemIds, 0, 3) as $seq => $itemId) {
                    RequisitionItem::query()->create([
                        'requisition_id' => $requisition->id, 'item_id' => $itemId,
                        'request_quantity' => 100, 'revised_quantity' => 100, 'due_quantity' => 100,
                        'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                    ]);
                }
            }

            if ($i > 4) {
                continue; // remaining requisitions stay pending, no PO yet.
            }

            $poNo = 'PO-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $po = PurchaseOrder::query()->firstOrCreate(
                ['po_number' => $poNo],
                [
                    'supplier_id' => $this->supplierIds[$i % count($this->supplierIds)],
                    'branch_id' => $this->branchId,
                    'order_date' => Carbon::now()->subDays(20 - $i * 2)->toDateString(),
                    'expected_delivery_date' => Carbon::now()->subDays(10 - $i)->toDateString(),
                    'po_status' => $i <= 2 ? 'completed' : 'approved',
                    'process_status' => 'APPROVED',
                    'requisition_id' => $requisition->id,
                    'approved_by' => $this->actorId, 'approved_at' => Carbon::now()->subDays(18 - $i),
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ],
            );

            if (!$po->wasRecentlyCreated) {
                continue;
            }

            $poItems = [];
            foreach (array_slice($this->itemIds, 0, 3) as $itemId) {
                $unitPrice = rand(20, 200);
                $qty = 100;
                $poItems[] = PurchaseOrderItem::query()->create([
                    'purchase_order_id' => $po->id, 'item_id' => $itemId,
                    'quantity' => $qty, 'unit_price' => $unitPrice, 'line_total' => $qty * $unitPrice,
                    'received_quantity' => $i <= 2 ? $qty : 0,
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ]);
            }

            if ($i > 2) {
                continue; // first two POs are fully received via a GRN; rest stay pending receipt.
            }

            $grn = GoodsReceiveNote::query()->create([
                'grn_number' => 'GRN-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'branch_id' => $this->branchId, 'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->id, 'logistic_id' => $this->logisticId,
                'ref_po_number' => $poNo, 'ref_po_date' => $po->order_date,
                'ref_challan_no' => 'CH-' . $i, 'ref_challan_date' => Carbon::now()->subDays(9 - $i)->toDateString(),
                'received_date' => Carbon::now()->subDays(8 - $i)->toDateString(),
                'process_status' => 'Approved',
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            foreach ($poItems as $poItem) {
                $grnItem = GoodsReceiveNoteItem::query()->create([
                    'goods_receive_note_id' => $grn->id, 'item_id' => $poItem->item_id,
                    'unit_price' => $poItem->unit_price, 'quantity' => $poItem->quantity,
                    'total_price' => $poItem->unit_price * $poItem->quantity,
                    'shelve_id' => $this->shelveId,
                    'expire_date' => Carbon::now()->addYear()->toDateString(),
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ]);

                ItemStock::query()->create([
                    'branch_id' => $this->branchId, 'item_id' => $poItem->item_id, 'shelve_id' => $this->shelveId,
                    'unit_price' => $poItem->unit_price, 'quantity' => $poItem->quantity, 'balance_quantity' => $poItem->quantity,
                    'recordable_id' => $grn->id, 'recordable_type' => GoodsReceiveNote::class,
                    'action_from' => 'GRN', 'expire_date' => Carbon::now()->addYear()->toDateString(),
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ]);
            }
        }

        $this->command->info('[InventoryDemoSeeder] Requisitions: ' . Requisition::query()->count() . ', POs: ' . PurchaseOrder::query()->count() . ', GRNs: ' . GoodsReceiveNote::query()->count());
    }

    private function seedStockAdjustmentsAndTransfers(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $number = 'ADJ-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $adj = StockAdjustment::query()->firstOrCreate(
                ['stock_adjustment_number' => $number],
                [
                    'branch_id' => $this->branchId, 'reason' => 'Physical count variance (demo)',
                    'adjustment_type' => $i % 2 === 0 ? 'DECREASE' : 'INCREASE', 'process_status' => 'SUBMITTED',
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ],
            );
            if ($adj->wasRecentlyCreated) {
                StockAdjustmentItem::query()->create([
                    'stock_adjustment_id' => $adj->id, 'item_id' => $this->itemIds[$i % count($this->itemIds)],
                    'quantity' => rand(5, 20), 'shelve_id' => $this->shelveId,
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ]);
            }
        }

        for ($i = 1; $i <= 2; $i++) {
            $number = 'TRF-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $transfer = StockTransfer::query()->firstOrCreate(
                ['stock_transfer_number' => $number],
                [
                    'transfer_from' => $this->branchId, 'transfer_to' => [(string) Branch::query()->where('name', 'Main Hospital Campus')->value('id')],
                    'reason' => 'Ward-level restock (demo)', 'process_status' => 'SUBMITTED',
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ],
            );
            if ($transfer->wasRecentlyCreated) {
                StockTransferItem::query()->create([
                    'stock_transfer_id' => $transfer->id, 'item_id' => $this->itemIds[$i % count($this->itemIds)],
                    'quantity' => rand(10, 30),
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ]);
            }
        }

        $this->command->info('[InventoryDemoSeeder] Stock adjustments: ' . StockAdjustment::query()->count() . ', transfers: ' . StockTransfer::query()->count());
    }

    private function seedVendorQuotesAndRateContracts(): void
    {
        $itemId = $this->itemIds[0];
        $created = 0;

        foreach ($this->supplierIds as $i => $supplierId) {
            $exists = DB::table('vendor_quotes')->where(['item_id' => $itemId, 'supplier_id' => $supplierId])->exists();
            if ($exists) {
                continue;
            }
            $quote = VendorQuote::query()->create([
                'supplier_id' => $supplierId, 'item_id' => $itemId,
                'quoted_unit_price' => rand(20, 60), 'quoted_delivery_days' => rand(3, 14),
                'is_selected' => $i === 0, 'submitted_at' => now(),
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);
            $created++;

            if ($i === 0) {
                RateContract::query()->firstOrCreate(
                    ['supplier_id' => $supplierId, 'item_id' => $itemId],
                    [
                        'vendor_quote_id' => $quote->id, 'contract_price' => $quote->quoted_unit_price,
                        'valid_from' => Carbon::now()->toDateString(), 'valid_to' => Carbon::now()->addYear()->toDateString(),
                        'contract_status' => 'active', 'process_status' => 'APPROVED',
                        'approved_by' => $this->actorId, 'approved_at' => now(),
                        'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                    ],
                );
            }
        }

        $this->command->info("[InventoryDemoSeeder] Vendor quotes created: {$created}");
    }
}
