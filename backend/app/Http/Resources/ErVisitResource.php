<?php

namespace App\Http\Resources;

use App\Enums\ErVisitStatusEnum;

class ErVisitResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;
            $status = $this->er_status;

            $data = [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'er_visit_no'          => $this->er_visit_no,
                'patient_id'           => $this->patient_id,
                'arrival_mode'         => $this->arrival_mode,
                'chief_complaint'      => $this->chief_complaint,
                'arrival_at'           => $this->arrival_at,
                'er_status'            => $status,
                'er_status_label'      => ErVisitStatusEnum::label($status),
                'disposition'          => $this->disposition,
                'linked_admission_id'  => $this->linked_admission_id,
                'disposed_at'          => $this->disposed_at,
                'registered_by'        => $this->registered_by,
                'created_by_name'      => $baseData['created_by_name'] ?? null,
                'updated_by_name'      => $baseData['updated_by_name'] ?? null,
                'created_at'           => $baseData['created_at'] ?? null,
                'updated_at'           => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('patient') && $resource->patient) {
                $data['patient_name'] = trim(($resource->patient->first_name ?? '') . ' ' . ($resource->patient->last_name ?? ''));
                $data['mrn'] = $resource->patient->mrn ?? null;
            }

            if ($resource->relationLoaded('triages')) {
                $data['triages'] = ErTriageResource::collection($resource->triages)->toArray($request);
                $data['current_triage'] = $resource->triages->isNotEmpty()
                    ? (new ErTriageResource($resource->triages->first()))->resolve()
                    : null;
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
