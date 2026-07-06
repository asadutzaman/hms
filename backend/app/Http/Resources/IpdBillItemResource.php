<?php

namespace App\Http\Resources;

use App\Enums\IpdBillItemTypeEnum;

class IpdBillItemResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $itemType = $resource->item_type ?? '';

            $data = [
                'id'              => $this->id,
                'uuid'            => $this->uuid,
                'ipd_bill_id'     => $this->ipd_bill_id,
                'item_type'       => $itemType,
                'item_type_label' => IpdBillItemTypeEnum::label($itemType),
                'description'     => $this->description,
                'quantity'        => $this->quantity,
                'unit_price'      => $this->unit_price,
                'amount'          => $this->line_total,
                'source_type'     => $this->source_type,
                'source_id'       => $this->source_id,
                'sequence'        => $this->sequence,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'      => $baseData['created_at'] ?? null,
                'updated_at'      => $baseData['updated_at'] ?? null,
            ];

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
