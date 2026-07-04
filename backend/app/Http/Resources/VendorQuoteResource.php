<?php

namespace App\Http\Resources;

class VendorQuoteResource extends BaseResource
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

            $data = [
                'id'                    => $this->id,
                'purchase_order_id'     => $this->purchase_order_id,
                'requisition_id'        => $this->requisition_id,
                'supplier_id'           => $this->supplier_id,
                'supplier_name'         => optional($this->supplier)->supplier_name,
                'item_id'               => $this->item_id,
                'item_name'             => optional($this->itemInfo)->name_en,
                'item_code'             => optional($this->itemInfo)->code,
                'quoted_unit_price'     => $this->quoted_unit_price,
                'quoted_delivery_days'  => $this->quoted_delivery_days,
                'is_selected'           => $this->is_selected,
                'notes'                 => $this->notes,
                'submitted_at'          => $this->submitted_at,
                'status'                => $this->status,
                'created_by_name'       => $baseData['created_by_name'] ?? null,
                'updated_by_name'       => $baseData['updated_by_name'] ?? null,
            ];

            return $data;
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}
