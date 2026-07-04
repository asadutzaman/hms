<?php

namespace App\Http\Resources;

class PatientAllergyResource extends BaseResource
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
                'id'              => $this->id,
                'patient_id'      => $this->patient_id,
                'allergy_type'    => $this->allergy_type,
                'allergen_name'   => $this->allergen_name,
                'drug_id'         => $this->drug_id,
                'drug_name'       => optional($this->drug)->name_en,
                'reaction_type'   => $this->reaction_type,
                'severity'        => $this->severity,
                'notes'           => $this->notes,
                'recorded_by'     => $this->recorded_by,
                'recorded_at'     => $this->recorded_at,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
            ];

            return $data;
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}
