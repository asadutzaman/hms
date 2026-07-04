<?php

namespace App\Http\Resources;

class RateContractResource extends BaseResource
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
                'id'               => $this->id,
                'supplier_id'      => $this->supplier_id,
                'supplier_name'    => optional($this->supplier)->supplier_name,
                'item_id'          => $this->item_id,
                'item_name'        => optional($this->itemInfo)->name_en,
                'item_code'        => optional($this->itemInfo)->code,
                'vendor_quote_id'  => $this->vendor_quote_id,
                'contract_price'   => $this->contract_price,
                'valid_from'       => $this->valid_from,
                'valid_to'         => $this->valid_to,
                'contract_status'  => $this->contract_status,
                'process_status'   => $this->process_status,
                'approved_by'      => $this->approved_by,
                'approved_at'      => $this->approved_at,
                'status'           => $this->status,
                'created_by_name'  => $baseData['created_by_name'] ?? null,
                'updated_by_name'  => $baseData['updated_by_name'] ?? null,
                'created_at'       => $baseData['created_at'] ?? null,
                'updated_at'       => $baseData['updated_at'] ?? null,
            ];

            return $data;
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}
