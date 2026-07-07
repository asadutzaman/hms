<?php

namespace App\Http\Resources;

class RadiologyReportTemplateResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);

            return [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'name'                 => $this->name,
                'modality'             => $this->modality,
                'body_part'            => $this->body_part,
                'findings_template'    => $this->findings_template,
                'impression_template'  => $this->impression_template,
                'is_active'            => (bool) $this->is_active,
                'status'               => $this->status,
                'created_by_name'      => $baseData['created_by_name'] ?? null,
                'updated_by_name'      => $baseData['updated_by_name'] ?? null,
                'created_at'           => $baseData['created_at'] ?? null,
                'updated_at'           => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
