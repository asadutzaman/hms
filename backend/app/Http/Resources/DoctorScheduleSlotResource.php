<?php

namespace App\Http\Resources;

use App\Enums\StatusEnum;

class DoctorScheduleSlotResource extends BaseResource
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

            $dayLabels = [
                0 => 'Sunday',
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ];

            $dayOfWeek = (int) ($resource->day_of_week ?? 0);

            $data = [
                'id'                    => $this->id,
                'uuid'                  => $this->uuid,
                'doctor_schedule_id'    => $this->doctor_schedule_id,
                'day_of_week'           => $dayOfWeek,
                'day_label'             => $dayLabels[$dayOfWeek] ?? '',
                'start_time'            => $this->start_time,
                'end_time'              => $this->end_time,
                'slot_duration_minutes' => $this->slot_duration_minutes,
                'max_patients_per_slot' => $this->max_patients_per_slot,
                'session_label'         => $this->session_label,
                'is_active'             => (bool) $this->is_active,
                'sort_order'            => $this->sort_order,
                'status'                => $this->status,
                'status_label'          => StatusEnum::label($this->status ?? null),
                'created_by_name'       => $baseData['created_by_name'],
                'updated_by_name'       => $baseData['updated_by_name'],
                'created_at'            => $baseData['created_at'],
                'updated_at'            => $baseData['updated_at'],
            ];

            return $data;
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}