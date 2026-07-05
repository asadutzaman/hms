<?php

namespace App\Http\Resources;

class PrescriptionDispenseResource extends BaseResource
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
                'id'                  => $this->id,
                'opd_prescription_id' => $this->opd_prescription_id,
                'patient_id'          => $this->patient_id,
                'branch_id'           => $this->branch_id,
                'dispensed_by'        => $this->dispensed_by,
                'dispensed_at'        => $this->dispensed_at,
                'dispense_status'     => $this->dispense_status,
                'notes'               => $this->notes,
                'status'              => $this->status,
                'created_by_name'     => $baseData['created_by_name'],
                'updated_by_name'     => $baseData['updated_by_name'],
            ];

            if ($this->branch) {
                $includesData['branch_name'] = $this->branch->name;
            }
            if ($this->dispenser) {
                $includesData['dispensed_by_name'] = $this->dispenser->name;
            }
            if (!$this->isCollection && $this->relationLoaded('items')) {
                $includesData['items'] = PrescriptionDispenseItemResource::collection($this->items);
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
