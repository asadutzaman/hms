<?php

namespace App\Services\Inventory;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class PurchaseOrderService
{
    /**
     * Called after a GRN raised against a PO is approved. Increments each
     * matching PO line's received_quantity by what was just received, then
     * flips the PO to partially_received / completed as appropriate.
     */
    public function applyReceipt(int $purchaseOrderId, array $receivedItems): void
    {
        foreach ($receivedItems as $item) {
            $poItem = PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
                ->where('item_id', $item['item_id'])
                ->first();
            if ($poItem) {
                $poItem->received_quantity = (float) $poItem->received_quantity + (float) $item['quantity'];
                $poItem->save();
            }
        }

        $this->refreshStatus($purchaseOrderId);
    }

    protected function refreshStatus(int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::find($purchaseOrderId);
        if (!$purchaseOrder) {
            return;
        }

        if ($this->isFullyReceived($purchaseOrderId)) {
            $purchaseOrder->po_status = 'completed';
        } else {
            $purchaseOrder->po_status = 'partially_received';
        }
        $purchaseOrder->save();
    }

    public function isFullyReceived(int $purchaseOrderId): bool
    {
        return !PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
            ->whereColumn('received_quantity', '<', 'quantity')
            ->exists();
    }
}
