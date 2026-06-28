<?php

namespace App\Validators;

use App\Enums\OpdVisitStatusEnum;
use Illuminate\Validation\Rule;

class OpdVisitValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return [
                    'patient_id'            => ['required', 'integer', 'exists:patients,id'],
                    'appointment_id'        => ['nullable', 'integer', 'exists:appointments,id'],
                    'doctor_id'             => ['required', 'integer', 'exists:employees,id'],
                    'department_id'         => ['required', 'integer', 'exists:departments,id'],
                    'visit_type'            => ['required', Rule::in(['walk_in', 'appointment', 'follow_up', 'emergency'])],
                    'visit_date'            => ['required', 'date_format:Y-m-d'],
                    'token_number'          => ['nullable', 'integer', 'min:1', 'max:9999'],
                    'status'                => ['nullable', Rule::in(OpdVisitStatusEnum::getKeys())],
                    'chief_complaint'       => ['nullable', 'string', 'max:1000'],
                    'history'               => ['nullable', 'string', 'max:5000'],
                    'examination'           => ['nullable', 'string', 'max:5000'],
                    'clinical_notes'        => ['nullable', 'string', 'max:5000'],
                    'advice'                => ['nullable', 'string', 'max:2000'],
                    'consultation_start_at' => ['nullable', 'date'],
                    'consultation_end_at'   => ['nullable', 'date', 'after_or_equal:consultation_start_at'],
                    'cancellation_reason'   => ['nullable', 'string', 'max:500'],
                    'cancelled_at'          => ['nullable', 'date'],
                    'closed_by'             => ['nullable', 'integer', 'exists:users,id'],
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'patient_id'            => ['nullable', 'integer', 'exists:patients,id'],
                    'appointment_id'        => ['nullable', 'integer', 'exists:appointments,id'],
                    'doctor_id'             => ['nullable', 'integer', 'exists:employees,id'],
                    'department_id'         => ['nullable', 'integer', 'exists:departments,id'],
                    'visit_type'            => ['nullable', Rule::in(['walk_in', 'appointment', 'follow_up', 'emergency'])],
                    'visit_date'            => ['nullable', 'date_format:Y-m-d'],
                    'token_number'          => ['nullable', 'integer', 'min:1', 'max:9999'],
                    'status'                => ['nullable', Rule::in(OpdVisitStatusEnum::getKeys())],
                    'chief_complaint'       => ['nullable', 'string', 'max:1000'],
                    'history'               => ['nullable', 'string', 'max:5000'],
                    'examination'           => ['nullable', 'string', 'max:5000'],
                    'clinical_notes'        => ['nullable', 'string', 'max:5000'],
                    'advice'                => ['nullable', 'string', 'max:2000'],
                    'consultation_start_at' => ['nullable', 'date'],
                    'consultation_end_at'   => ['nullable', 'date', 'after_or_equal:consultation_start_at'],
                    'closed_at'             => ['nullable', 'date'],
                    'closed_by'             => ['nullable', 'integer', 'exists:users,id'],
                    'cancellation_reason'   => ['nullable', 'string', 'max:500'],
                    'cancelled_at'          => ['nullable', 'date'],
                ];

            default:
                return [];
        }
    }

    public function messages()
    {
        $messages = parent::messages();
        $extra = [
            'patient_id.required'             => 'Patient is required.',
            'patient_id.exists'               => 'Selected patient does not exist.',
            'doctor_id.required'              => 'Doctor is required.',
            'doctor_id.exists'                => 'Selected doctor does not exist.',
            'department_id.required'          => 'Department is required.',
            'department_id.exists'            => 'Selected department does not exist.',
            'appointment_id.exists'           => 'Selected appointment does not exist.',
            'visit_type.required'             => 'Visit type is required.',
            'visit_type.in'                   => 'Visit type must be one of: walk_in, appointment, follow_up, emergency.',
            'visit_date.required'             => 'Visit date is required.',
            'visit_date.date_format'          => 'Visit date must be in YYYY-MM-DD format.',
            'status.in'                       => 'Invalid status. Allowed: ' . implode(', ', OpdVisitStatusEnum::getKeys()) . '.',
            'consultation_end_at.after_or_equal' => 'Consultation end must be at or after consultation start.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'patient_id'            => 'Patient',
            'doctor_id'             => 'Doctor',
            'department_id'         => 'Department',
            'appointment_id'        => 'Appointment',
            'visit_type'            => 'Visit Type',
            'visit_date'            => 'Visit Date',
            'token_number'          => 'Token Number',
            'chief_complaint'       => 'Chief Complaint',
            'history'               => 'History',
            'examination'           => 'Examination',
            'clinical_notes'        => 'Clinical Notes',
            'advice'                => 'Advice',
            'consultation_start_at' => 'Consultation Start',
            'consultation_end_at'   => 'Consultation End',
            'cancellation_reason'   => 'Cancellation Reason',
            'closed_by'             => 'Closed By',
        ];
        return array_merge($attributes, $extra);
    }
}
