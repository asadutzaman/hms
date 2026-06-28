<?php

namespace App\Http\Resources;

use App\Enums\AppointmentStatusEnum;
use App\Enums\AppointmentTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Repositories\AppointmentSlotRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\PatientRepository;
use App\Services\ApiHelperService;

class AppointmentResource extends BaseResource
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
            $responses = [];

            // Patient info
            if (!empty($resource->patient_id)) {
                $patient = (new PatientRepository())->getById($resource->patient_id);
                $includesData['patient_name']  = $patient ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) : '';
                $includesData['patient_mrn']   = $patient->mrn ?? '';
                $includesData['patient_phone'] = $patient->mobile ?? '';
            }

            // Doctor info
            if (!empty($resource->doctor_id)) {
                $doctor = (new EmployeeRepository())->getById($resource->doctor_id);
                $includesData['doctor_name']      = $doctor->name_en ?? '';
                $includesData['doctor_name_bn']   = $doctor->name_bn ?? '';
                $includesData['doctor_employee_id'] = $doctor->employee_id ?? '';
            }

            // Department info
            if (!empty($resource->department_id)) {
                $dept = (new DepartmentRepository())->getById($resource->department_id);
                $includesData['department_name']    = $dept->name_en ?? '';
                $includesData['department_name_bn'] = $dept->name_bn ?? '';
            }

            // Slot info (start/end time + available capacity)
            if (!empty($resource->appointment_slot_id)) {
                $slot = (new AppointmentSlotRepository())->getById($resource->appointment_slot_id);
                if ($slot) {
                    $includesData['slot_start_time']   = $slot->start_time ?? null;
                    $includesData['slot_end_time']     = $slot->end_time ?? null;
                    $includesData['slot_available']   = max(($slot->max_capacity ?? 0) - ($slot->booked_count ?? 0), 0);
                }
            }

            // Enum labels
            $includesData['status_label']         = AppointmentStatusEnum::label($resource->status ?? null);
            $includesData['type_label']           = AppointmentTypeEnum::label($resource->type ?? null);
            $includesData['payment_status_label'] = PaymentStatusEnum::label($resource->payment_status ?? null);

            $data = [
                'id'                  => $this->id,
                'uuid'                => $this->uuid,
                'appointment_no'      => $this->appointment_no,
                'patient_id'          => $this->patient_id,
                'doctor_id'           => $this->doctor_id,
                'department_id'       => $this->department_id,
                'appointment_slot_id' => $this->appointment_slot_id,
                'appointment_date'    => $this->appointment_date,
                'appointment_time'    => $this->appointment_time,
                'end_time'            => $this->end_time,
                'duration_minutes'    => $this->duration_minutes,
                'type'                => $this->type,
                'status'              => $this->status,
                'token_number'        => $this->token_number,
                'consultation_fee'    => $this->consultation_fee,
                'payment_status'      => $this->payment_status,
                'reason'              => $this->reason,
                'notes'               => $this->notes,
                'source'              => $this->source,
                'cancellation_reason' => $this->cancellation_reason,
                'rescheduled_from_id' => $this->rescheduled_from_id,
                'checked_in_at'       => $this->checked_in_at,
                'completed_at'        => $this->completed_at,
                'cancelled_at'        => $this->cancelled_at,
                'created_by_name'     => $baseData['created_by_name'],
                'updated_by_name'     => $baseData['updated_by_name'],
                'created_at'          => $baseData['created_at'],
                'updated_at'          => $baseData['updated_at'],
            ];

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }
}