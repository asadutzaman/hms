<?php

namespace App\Http\Resources;

class OpdDiagnosisResource extends BaseResource
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

            $diagnosisTypeLabel = ucfirst((string) ($resource->diagnosis_type ?? ''));

            $data = [
                'id'                => $this->id,
                'uuid'              => $this->uuid,
                'opd_visit_id'      => $this->opd_visit_id,
                'patient_id'        => $this->patient_id,
                'icd10_code'        => $this->icd10_code,
                'icd10_description' => $this->icd10_description,
                'diagnosis_name'    => $this->diagnosis_name,
                'diagnosis_type'    => $this->diagnosis_type,
                'sequence'          => $this->sequence,
                'notes'             => $this->notes,
                'is_primary'        => (bool) $this->is_primary,
                'is_chronic'        => (bool) $this->is_chronic,
                'is_confirmed'      => (bool) $this->is_confirmed,
                'recorded_by'       => $this->recorded_by,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            return array_merge($data, [
                'diagnosis_type_label' => $diagnosisTypeLabel,
            ]);
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
