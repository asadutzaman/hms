<?php

namespace App\Http\Resources;

use App\Enums\LabSampleStatusEnum;
use App\Repositories\UserRepository;

class LabSampleResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $status = $this->sample_status;

            $collectedByName = '';
            if (!empty($this->collected_by)) {
                $user = (new UserRepository())->getById($this->collected_by);
                $collectedByName = $user->name ?? '';
            }

            return [
                'id'                => $this->id,
                'uuid'              => $this->uuid,
                'lab_order_id'      => $this->lab_order_id,
                'barcode'           => $this->barcode,
                'sample_type'       => $this->sample_type,
                'sample_status'     => $status,
                'sample_status_label' => LabSampleStatusEnum::label($status),
                'collected_by'      => $this->collected_by,
                'collected_by_name' => $collectedByName,
                'collected_at'      => $this->collected_at,
                'received_by'       => $this->received_by,
                'received_at'       => $this->received_at,
                'rejection_reason'  => $this->rejection_reason,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
