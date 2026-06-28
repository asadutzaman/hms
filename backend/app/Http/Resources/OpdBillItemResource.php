<?php

namespace App\Http\Resources;

class OpdBillItemResource extends BaseResource
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
            $resource = $this->resource;

            $itemType = $resource->item_type ?? '';
            $itemTypeLabel = ucfirst(str_replace('_', ' ', (string) $itemType));

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'bill_id'        => $this->bill_id,
                'item_type'      => $itemType,
                'item_type_label' => $itemTypeLabel,
                'description'    => $this->description,
                'code'           => $this->code,
                'quantity'       => $this->quantity,
                'unit_price'     => $this->unit_price,
                'amount'         => $this->amount,
                'sequence'       => $this->sequence,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            // Backward/forward compatibility: model may use reference_type/reference_id (newer)
            // while migration uses source_type/source_id. Expose both if present.
            $extras = [];
            foreach (['reference_type', 'reference_id', 'source_type', 'source_id'] as $col) {
                if (array_key_exists($col, $baseData) || isset($resource->$col)) {
                    $extras[$col] = $resource->$col ?? null;
                }
            }

            return array_merge($data, $extras);
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
