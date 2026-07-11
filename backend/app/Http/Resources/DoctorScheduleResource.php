<?php

namespace App\Http\Resources;

use App\Enums\StatusEnum;
use App\Repositories\DepartmentRepository;
use App\Repositories\EmployeeRepository;

class DoctorScheduleResource extends BaseResource
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
                $doctor = (new EmployeeRepository())->getById($resource->doctor_id);
                $includesData['doctor_name']        = $doctor->name_en ?? '';
                $includesData['doctor_name_bn']     = $doctor->name_bn ?? '';
                $includesData['doctor_employee_id'] = $doctor->employee_id ?? '';
            }

            if (!empty($resource->department_id)) {
                $dept = (new DepartmentRepository())->getById($resource->department_id);
                $includesData['department_name']    = $dept->name_en ?? '';
                $includesData['department_name_bn'] = $dept->name_bn ?? '';
            }

            if (!empty($resource->chamber_id)) {
                $branch = (new \App\Repositories\BranchRepository())->getById($resource->chamber_id);
                $includesData['chamber_name'] = $branch->name ?? '';
            }

            $includesData['status_label']    = StatusEnum::getValue($resource->status ?? null);
            $includesData['schedule_type_label']    = $resource->schedule_type;
            $includesData['consultation_mode_label'] = $resource->consultation_mode;
            $includesData['slot_count']      = $resource->slots()->count() ?? 0;
            $includesData['exception_count'] = $resource->exceptions()->count() ?? 0;

            $data = [
                'id'                      => $this->id,
                'uuid'                    => $this->uuid,
                'name'                    => $this->name,
                'doctor_id'               => $this->doctor_id,
                'department_id'           => $this->department_id,
                'chamber_id'              => $this->chamber_id,
                'schedule_type'           => $this->schedule_type,
                'consultation_mode'       => $this->consultation_mode,
                'effective_from'          => $this->effective_from,
                'effective_to'            => $this->effective_to,
                'slot_duration_minutes'   => $this->slot_duration_minutes,
                'max_patients_per_slot'   => $this->max_patients_per_slot,
                'buffer_minutes'          => $this->buffer_minutes,
                'consultation_fee'        => $this->consultation_fee,
                'follow_up_fee'           => $this->follow_up_fee,
                'currency'                => $this->currency,
                'is_recurring'            => (bool) $this->is_recurring,
                'allow_online_booking'    => (bool) $this->allow_online_booking,
                'allow_walk_in'           => (bool) $this->allow_walk_in,
                'is_default'              => (bool) $this->is_default,
                'notes'                   => $this->notes,
                'sort_order'              => $this->sort_order,
                'status'                  => $this->status,
                'created_by_name'         => $baseData['created_by_name'],
                'updated_by_name'         => $baseData['updated_by_name'],
                'created_at'              => $baseData['created_at'],
                'updated_at'              => $baseData['updated_at'],
            ];

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}