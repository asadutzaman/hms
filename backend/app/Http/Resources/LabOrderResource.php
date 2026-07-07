<?php

namespace App\Http\Resources;

use App\Enums\LabOrderStatusEnum;

class LabOrderResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;
            $status = $this->order_status;

            $data = [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'lab_order_no'         => $this->lab_order_no,
                'patient_id'           => $this->patient_id,
                'opd_visit_id'         => $this->opd_visit_id,
                'ipd_admission_id'     => $this->ipd_admission_id,
                'order_source'         => $this->order_source,
                'ordered_by'           => $this->ordered_by,
                'ordered_at'           => $this->ordered_at,
                'priority'             => $this->priority,
                'clinical_indication'  => $this->clinical_indication,
                'order_status'         => $status,
                'order_status_label'   => LabOrderStatusEnum::label($status),
                'created_by_name'      => $baseData['created_by_name'] ?? null,
                'updated_by_name'      => $baseData['updated_by_name'] ?? null,
                'created_at'           => $baseData['created_at'] ?? null,
                'updated_at'           => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('patient') && $resource->patient) {
                $data['patient_name'] = trim(($resource->patient->first_name ?? '') . ' ' . ($resource->patient->last_name ?? ''));
                $data['mrn'] = $resource->patient->mrn ?? null;
            }
            if ($resource->relationLoaded('items')) {
                $data['items'] = LabOrderItemResource::collection($resource->items)->toArray($request);
            }
            if ($resource->relationLoaded('samples')) {
                $data['samples'] = LabSampleResource::collection($resource->samples)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
