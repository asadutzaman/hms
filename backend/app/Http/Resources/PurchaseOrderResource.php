<?php

namespace App\Http\Resources;

use App\Repositories\PurchaseOrderItemRepository;

class PurchaseOrderResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);

            $includesData = [];

            $data = [
                'id'                      => $this->id,
                'po_number'               => $this->po_number,
                'supplier_id'             => $this->supplier_id,
                'branch_id'               => $this->branch_id,
                'order_date'              => $this->order_date,
                'expected_delivery_date'  => $this->expected_delivery_date,
                'po_status'               => $this->po_status,
                'process_status'          => $this->process_status,
                'notes'                   => $this->notes,
                'requisition_id'          => $this->requisition_id,
                'approved_by'             => $this->approved_by,
                'approved_at'             => $this->approved_at,
                'status'                  => $this->status,
                'created_by_name'         => $baseData['created_by_name'],
                'updated_by_name'         => $baseData['updated_by_name'],
                'created_at'              => $baseData['created_at'],
                'updated_at'              => $baseData['updated_at'],
            ];

            if ($this->supplier) {
                $includesData['supplier_name'] = $this->supplier->supplier_name;
            }
            if ($this->branch) {
                $includesData['branch_name'] = $this->branch->name;
            }

            if (!$this->isCollection) {
                $includesData['purchase_order_items_list_data'] = (new PurchaseOrderItemRepository())
                    ->newQuery()
                    ->select('id', 'unit_price', 'quantity', 'line_total', 'received_quantity', 'remarks', 'item_id')
                    ->with([
                        'itemInfo:id,name_en,name_bn,code',
                    ])
                    ->where('purchase_order_id', $this->id)
                    ->get();
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}
