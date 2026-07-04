<?php

namespace App\Http\Resources;

class DrugResource extends BaseResource
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
            $item = $this->item;

            $data = [
                'id'                  => $this->id,
                'uuid'                => $this->uuid,
                'item_id'             => $this->item_id,
                'code'                => $item->code ?? null,
                'name_en'             => $item->name_en ?? null,
                'name_bn'             => $item->name_bn ?? null,
                'description'         => $item->description ?? null,
                'logistic_id'         => $item->logistic_id ?? null,
                'item_category_id'    => $item->item_category_id ?? null,
                'brand_id'            => $item->brand_id ?? null,
                'base_unit_id'        => $item->base_unit_id ?? null,
                'reorder_qty'         => $item->reorder_qty ?? null,
                'generic_name'        => $this->generic_name,
                'brand_name'          => $this->brand_name,
                'strength'            => $this->strength,
                'dosage_form'         => $this->dosage_form,
                'hsn_code'            => $this->hsn_code,
                'is_controlled'       => (bool) $this->is_controlled,
                'controlled_schedule' => $this->controlled_schedule,
                'generic_drug_id'     => $this->generic_drug_id,
                'is_generic'          => empty($this->generic_drug_id),
                'status'              => $this->status,
                'created_at'          => $baseData['created_at'] ?? null,
                'updated_at'          => $baseData['updated_at'] ?? null,
                'created_by_name'     => $baseData['created_by_name'] ?? null,
                'updated_by_name'     => $baseData['updated_by_name'] ?? null,
            ];

            $includesData = [];
            if ($item?->itemCategory) {
                $includesData['item_category_name'] = $item->itemCategory->name;
            }
            if ($item?->brand) {
                $includesData['brand_master_name'] = $item->brand->name;
            }
            if ($item?->baseUnit) {
                $includesData['base_unit_short_name'] = $item->baseUnit->short_name;
            }
            if ($this->generic) {
                $includesData['generic_drug_name'] = $this->generic->generic_name;
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}
