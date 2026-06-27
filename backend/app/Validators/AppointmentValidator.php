<?php

namespace App\Validators;

use App\Enums\AppointmentStatusEnum;
use App\Enums\AppointmentTypeEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Validation\Rule;

class AppointmentValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $id = $this->request->route('id');

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return [
                    'patient_id'           => ['required', 'integer', 'exists:patients,id'],
                    'doctor_id'            => ['required', 'integer', 'exists:employees,id'],
                    'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
                    'appointment_slot_id'  => ['nullable', 'integer', 'exists:appointment_slots,id'],
                    'appointment_date'     => ['required', 'date'],
                    'appointment_time'     => ['required', 'date_format:H:i'],
                    'end_time'             => ['nullable', 'date_format:H:i', 'after:appointment_time'],
                    'duration_minutes'     => ['nullable', 'integer', 'min:5', 'max:240'],
                    'type'                 => ['required', Rule::in(AppointmentTypeEnum::getKeys())],
                    'status'               => ['nullable', Rule::in(AppointmentStatusEnum::getKeys())],
                    'consultation_fee'     => ['nullable', 'numeric', 'min:0'],
                    'payment_status'       => ['nullable', Rule::in(PaymentStatusEnum::getKeys())],
                    'reason'               => ['nullable', 'string', 'max:500'],
                    'notes'                => ['nullable', 'string', 'max:1000'],
                    'source'               => ['nullable', 'string', 'max:50'],
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'patient_id'           => ['nullable', 'integer', 'exists:patients,id'],
                    'doctor_id'            => ['nullable', 'integer', 'exists:employees,id'],
                    'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
                    'appointment_slot_id'  => ['nullable', 'integer', 'exists:appointment_slots,id'],
                    'appointment_date'     => ['required', 'date'],
                    'appointment_time'     => ['required', 'date_format:H:i'],
                    'end_time'             => ['nullable', 'date_format:H:i', 'after:appointment_time'],
                    'duration_minutes'     => ['nullable', 'integer', 'min:5', 'max:240'],
                    'type'                 => ['nullable', Rule::in(AppointmentTypeEnum::getKeys())],
                    'status'               => ['nullable', Rule::in(AppointmentStatusEnum::getKeys())],
                    'consultation_fee'     => ['nullable', 'numeric', 'min:0'],
                    'payment_status'       => ['nullable', Rule::in(PaymentStatusEnum::getKeys())],
                    'reason'               => ['nullable', 'string', 'max:500'],
                    'notes'                => ['nullable', 'string', 'max:1000'],
                    'cancellation_reason'  => ['nullable', 'string', 'max:500'],
                    'rescheduled_from_id'  => ['nullable', 'integer', 'exists:appointments,id'],
                ];

            default:
                return [];
        }
    }

    public function messages()
    {
        $messages = parent::messages();
        $includesMessages = [
            'patient_id.required'       => 'Patient is required.',
            'patient_id.exists'         => 'Selected patient does not exist.',
            'doctor_id.required'        => 'Doctor is required.',
            'doctor_id.exists'          => 'Selected doctor does not exist.',
            'appointment_date.required' => 'Appointment date is required.',
            'appointment_time.required' => 'Appointment time is required.',
            'appointment_time.date_format'=> 'Appointment time must be HH:MM.',
            'type.required'             => 'Appointment type is required.',
            'type.in'                   => 'Appointment type must be one of: ONLINE, WALK_IN, FOLLOW_UP.',
        ];
        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $includesAttributes = [
            'patient_id'           => 'Patient',
            'doctor_id'            => 'Doctor',
            'department_id'        => 'Department',
            'appointment_slot_id'  => 'Slot',
            'appointment_date'     => 'Appointment Date',
            'appointment_time'     => 'Appointment Time',
            'consultation_fee'     => 'Consultation Fee',
            'payment_status'       => 'Payment Status',
            'cancellation_reason'  => 'Cancellation Reason',
        ];
        return array_merge($attributes, $includesAttributes);
    }
}