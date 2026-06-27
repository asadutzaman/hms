<?php

namespace App\Http\Resources;

use App\Enums\AppointmentActionEnum;
use App\Repositories\UserRepository;

class AppointmentAuditLogResource extends BaseResource
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

            $includesData = [];
            $resource = $this->resource;

            if (!empty($resource->performed_by)) {
                $user = (new UserRepository())->getById($resource->performed_by);
                $includesData['performed_by_name'] = $user->name ?? '';
            }

            $includesData['action_label'] = AppointmentActionEnum::label($resource->action ?? null);

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'appointment_id' => $this->appointment_id,
                'action'         => $this->action,
                'old_values'     => $this->old_values,
                'new_values'     => $this->new_values,
                'remarks'        => $this->remarks,
                'performed_by'   => $this->performed_by,
                'performed_at'   => $this->performed_at,
                'created_by_name'=> $baseData['created_by_name'],
                'updated_by_name'=> $baseData['updated_by_name'],
                'created_at'     => $baseData['created_at'],
                'updated_at'     => $baseData['updated_at'],
            ];

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}