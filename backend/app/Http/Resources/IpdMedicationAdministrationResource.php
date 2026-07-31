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
            $order = $this->order;

            return [
                'id'                     => $this->id,
                'uuid'                   => $this->uuid,
                'order_id'               => $this->order_id,
                // Drug identity comes from the parent order — the MAR worklist
                // needs it to show what is actually being given.
                'drug_name'              => $order->drug_name ?? null,
                'generic_name'           => $order->generic_name ?? null,
                'strength'               => $order->strength ?? null,
                'dose_value'             => $order->dose_value ?? null,
                'dose_unit'              => $order->dose_unit ?? null,
                'route'                  => $order->route ?? null,
                'frequency'              => $order->frequency ?? null,
                'is_prn'                 => (bool) ($order->is_prn ?? false),
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
