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
                    // Field names follow the actual appointments table: start_time
                    // (not appointment_time), reason_for_visit (not reason), and
                    // there is no `type` column (source/consultation_mode cover it).
                    // doctor_id references users — doctors are users, not employees.
                    'patient_id'           => ['required', 'integer', 'exists:patients,id'],
                    'doctor_id'            => ['required', 'integer', 'exists:users,id'],
                    'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
                    'appointment_slot_id'  => ['nullable', 'integer', 'exists:appointment_slots,id'],
                    'appointment_date'     => ['required', 'date'],
                    'start_time'           => ['required', 'date_format:H:i'],
                    'end_time'             => ['nullable', 'date_format:H:i', 'after:start_time'],
                    'duration_minutes'     => ['nullable', 'integer', 'min:5', 'max:240'],
                    'consultation_mode'    => ['nullable', 'string', 'max:50'],
                    'status'               => ['nullable', Rule::in(AppointmentStatusEnum::getKeys())],
                    'consultation_fee'     => ['nullable', 'numeric', 'min:0'],
                    // Explicit list, not PaymentStatusEnum: that enum's values are
                    // uppercase and shared with IPD, while the appointments
                    // payment_status CHECK only accepts these lowercase values.
                    'payment_status'       => ['nullable', Rule::in(['unpaid', 'partial', 'paid', 'refunded', 'waived'])],
                    'reason_for_visit'     => ['nullable', 'string', 'max:500'],
                    'symptoms'             => ['nullable', 'string', 'max:1000'],
                    'notes'                => ['nullable', 'string', 'max:1000'],
                    'source'               => ['nullable', 'string', 'max:50'],
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'patient_id'           => ['nullable', 'integer', 'exists:patients,id'],
                    'doctor_id'            => ['nullable', 'integer', 'exists:users,id'],
                    'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
                    'appointment_slot_id'  => ['nullable', 'integer', 'exists:appointment_slots,id'],
                    'appointment_date'     => ['required', 'date'],
                    'start_time'           => ['required', 'date_format:H:i'],
                    'end_time'             => ['nullable', 'date_format:H:i', 'after:start_time'],
                    'duration_minutes'     => ['nullable', 'integer', 'min:5', 'max:240'],
                    'consultation_mode'    => ['nullable', 'string', 'max:50'],
                    'status'               => ['nullable', Rule::in(AppointmentStatusEnum::getKeys())],
                    'consultation_fee'     => ['nullable', 'numeric', 'min:0'],
                    // Explicit list, not PaymentStatusEnum: that enum's values are
                    // uppercase and shared with IPD, while the appointments
                    // payment_status CHECK only accepts these lowercase values.
                    'payment_status'       => ['nullable', Rule::in(['unpaid', 'partial', 'paid', 'refunded', 'waived'])],
                    'reason_for_visit'     => ['nullable', 'string', 'max:500'],
                    'symptoms'             => ['nullable', 'string', 'max:1000'],
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
            'start_time.required'       => 'Appointment time is required.',
            'start_time.date_format'    => 'Appointment time must be HH:MM.',
            'end_time.after'            => 'End time must be after the start time.',
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
            'start_time'           => 'Appointment Time',
            'consultation_fee'     => 'Consultation Fee',
            'payment_status'       => 'Payment Status',
            'cancellation_reason'  => 'Cancellation Reason',
        ];
        return array_merge($attributes, $includesAttributes);
    }
}