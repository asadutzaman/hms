<?php

namespace App\Http\Resources;

use App\Enums\StatusEnum;
use App\Repositories\UserRepository;

class AppointmentSlotResource extends BaseResource
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

            if (!empty($resource->doctor_id)) {
                // Doctor is now a User (was an Employee).
                $includesData['doctor_name']      = (new UserRepository())->getUserNameById($resource->doctor_id);
                $includesData['doctor_name_bn']   = '';
            }

            $includesData['available_capacity'] = max(($resource->max_capacity ?? 0) - ($resource->booked_count ?? 0), 0);
            $includesData['status_label']       = StatusEnum::label($resource->status ?? null);

            $data = [
                'id'              => $this->id,
                'uuid'            => $this->uuid,
                'schedule_id'     => $this->schedule_id,
                'doctor_id'       => $this->doctor_id,
                'slot_date'       => $this->slot_date,
                'start_time'      => $this->start_time,
                'end_time'        => $this->end_time,
                'duration_minutes'=> $this->duration_minutes,
                'max_capacity'    => $this->max_capacity,
                'booked_count'    => $this->booked_count,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'],
                'updated_by_name' => $baseData['updated_by_name'],
                'created_at'      => $baseData['created_at'],
                'updated_at'      => $baseData['updated_at'],
            ];

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}