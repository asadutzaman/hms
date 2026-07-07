<?php

namespace App\Http\Resources;

class BillingPackageItemResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            return [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'billing_package_id'   => $this->billing_package_id,
                'item_type'            => $this->item_type,
                'description'          => $this->description,
                'default_quantity'     => $this->default_quantity,
                'notional_unit_price'  => $this->notional_unit_price,
                'sequence'             => $this->sequence,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
