<?php

namespace App\Http\Resources;

use App\Enums\IpdMedicationOrderStatusEnum;

class IpdMedicationOrderResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;
            $status = $this->order_status;

            $data = [
                'id'                  => $this->id,
                'uuid'                => $this->uuid,
                'admission_id'        => $this->admission_id,
                'drug_id'             => $this->drug_id,
                'drug_name'           => $this->drug_name,
                'generic_name'        => $this->generic_name,
                'strength'            => $this->strength,
                'dosage_form'         => $this->dosage_form,
                'dose_value'          => $this->dose_value,
                'dose_unit'           => $this->dose_unit,
                'route'               => $this->route,
                'frequency'           => $this->frequency,
                'duration_value'      => $this->duration_value,
                'duration_unit'       => $this->duration_unit,
                'is_prn'              => (bool) $this->is_prn,
                'instruction'         => $this->instruction,
                'start_date'          => $this->start_date,
                'end_date'            => $this->end_date,
                'order_status'        => $status,
                'order_status_label'  => IpdMedicationOrderStatusEnum::label($status),
                'ordered_by'          => $this->ordered_by,
                'ordered_at'          => $this->ordered_at,
                'discontinued_by'     => $this->discontinued_by,
                'discontinued_at'     => $this->discontinued_at,
                'discontinue_reason'  => $this->discontinue_reason,
                'created_by_name'     => $baseData['created_by_name'] ?? null,
                'updated_by_name'     => $baseData['updated_by_name'] ?? null,
                'created_at'          => $baseData['created_at'] ?? null,
                'updated_at'          => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('administrations')) {
                $data['administrations'] = IpdMedicationAdministrationResource::collection($resource->administrations)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
