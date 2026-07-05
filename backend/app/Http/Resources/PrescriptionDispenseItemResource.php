<?php

namespace App\Http\Resources;

class PrescriptionDispenseItemResource extends BaseResource
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
            $includesData = [];
            $data = [
                'id'                            => $this->id,
                'opd_prescription_dispense_id'  => $this->opd_prescription_dispense_id,
                'opd_prescription_item_id'      => $this->opd_prescription_item_id,
                'drug_id'                       => $this->drug_id,
                'dispensed_quantity'            => $this->dispensed_quantity,
                'expire_date'                   => $this->expire_date,
                'remarks'                       => $this->remarks,
            ];

            if ($this->drug) {
                $includesData['drug_name'] = $this->drug->item->name_en ?? $this->drug->brand_name ?? null;
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
