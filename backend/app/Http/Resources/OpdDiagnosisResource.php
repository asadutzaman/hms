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
            $isPrimary = ($resource->diagnosis_type ?? null) === 'primary';

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'visit_id'       => $this->visit_id,
                'icd_code'       => $this->icd_code,
                'description'    => $this->description,
                'diagnosis_type' => $this->diagnosis_type,
                'sequence'       => $this->sequence,
                'remarks'        => $this->remarks,
                'is_primary'     => $isPrimary,
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
