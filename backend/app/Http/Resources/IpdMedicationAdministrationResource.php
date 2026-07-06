<?php

namespace App\Http\Resources;

use App\Enums\IpdMedicationAdministrationStatusEnum;

class IpdMedicationAdministrationResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $status = $this->administration_status;

            return [
                'id'                     => $this->id,
                'uuid'                   => $this->uuid,
                'order_id'               => $this->order_id,
                'scheduled_at'           => $this->scheduled_at,
                'administered_at'        => $this->administered_at,
                'administration_status'  => $status,
                'administration_status_label' => IpdMedicationAdministrationStatusEnum::label($status),
                'administered_by'        => $this->administered_by,
                'witnessed_by'           => $this->witnessed_by,
                'reason'                 => $this->reason,
                'notes'                  => $this->notes,
                'created_by_name'        => $baseData['created_by_name'] ?? null,
                'updated_by_name'        => $baseData['updated_by_name'] ?? null,
                'created_at'             => $baseData['created_at'] ?? null,
                'updated_at'             => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
