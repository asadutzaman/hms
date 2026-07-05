<?php

namespace App\Http\Resources;

class DrugInteractionResource extends BaseResource
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
                'id'              => $this->id,
                'drug_a_id'       => $this->drug_a_id,
                'drug_b_id'       => $this->drug_b_id,
                'severity'        => $this->severity,
                'description'     => $this->description,
                'recommendation'  => $this->recommendation,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'],
                'updated_by_name' => $baseData['updated_by_name'],
            ];

            if ($this->drugA) {
                $includesData['drug_a_name'] = $this->drugA->item->name_en ?? $this->drugA->brand_name ?? null;
            }
            if ($this->drugB) {
                $includesData['drug_b_name'] = $this->drugB->item->name_en ?? $this->drugB->brand_name ?? null;
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
