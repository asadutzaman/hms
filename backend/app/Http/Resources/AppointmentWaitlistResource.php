<?php

namespace App\Http\Resources;

use App\Enums\WaitlistStatusEnum;
use App\Repositories\EmployeeRepository;
use App\Repositories\PatientRepository;

class AppointmentWaitlistResource extends BaseResource
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

            if (!empty($resource->patient_id)) {
                $patient = (new PatientRepository())->getById($resource->patient_id);
                $includesData['patient_name']  = $patient ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) : '';
                $includesData['patient_mrn']   = $patient->mrn ?? '';
                $includesData['patient_phone'] = $patient->mobile ?? '';
            }

            if (!empty($resource->doctor_id)) {
                $doctor = (new EmployeeRepository())->getById($resource->doctor_id);
                $includesData['doctor_name']      = $doctor->name_en ?? '';
                $includesData['doctor_name_bn']   = $doctor->name_bn ?? '';
            }

            $includesData['status_label'] = WaitlistStatusEnum::label($resource->status ?? null);

            if (!empty($resource->created_at)) {
                $includesData['days_waiting'] = now()->diffInDays($resource->created_at);
            }

            $data = [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'patient_id'           => $this->patient_id,
                'doctor_id'            => $this->doctor_id,
                'preferred_date_from'  => $this->preferred_date_from,
                'preferred_date_to'    => $this->preferred_date_to,
                'preferred_time_slot'  => $this->preferred_time_slot,
                'priority'             => $this->priority,
                'status'               => $this->status,
                'notes'                => $this->notes,
                'notified_at'          => $this->notified_at,
                'converted_appointment_id' => $this->converted_appointment_id,
                'expired_at'           => $this->expired_at,
                'created_by_name'      => $baseData['created_by_name'],
                'updated_by_name'      => $baseData['updated_by_name'],
                'created_at'           => $baseData['created_at'],
                'updated_at'           => $baseData['updated_at'],
            ];

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}