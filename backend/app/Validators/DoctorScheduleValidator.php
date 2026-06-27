<?php

namespace App\Validators;

use App\Enums\StatusEnum;
use Illuminate\Validation\Rule;

class DoctorScheduleValidator extends BaseValidator
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
                    'doctor_id'              => ['required', 'integer', 'exists:employees,id'],
                    'department_id'          => ['nullable', 'integer', 'exists:departments,id'],
                    'schedule_name'          => ['required', 'string', 'max:191'],
                    'effective_from'         => ['required', 'date'],
                    'effective_to'           => ['nullable', 'date', 'after_or_equal:effective_from'],
                    'slot_duration_minutes'  => ['required', 'integer', 'min:5', 'max:240'],
                    'max_patients_per_slot'  => ['required', 'integer', 'min:1', 'max:100'],
                    'consultation_fee'       => ['nullable', 'numeric', 'min:0'],
                    'status'                 => ['nullable', Rule::in(StatusEnum::getKeys())],
                    'remarks'                => ['nullable', 'string'],

                    // Slots sub-array (optional on POST — slots can be added separately)
                    'slots'                  => ['nullable', 'array'],
                    'slots.*.day_of_week'    => ['required_with:slots', 'integer', 'between:0,6'],
                    'slots.*.start_time'     => ['required_with:slots', 'date_format:H:i'],
                    'slots.*.end_time'       => ['required_with:slots', 'date_format:H:i', 'after:slots.*.start_time'],
                    'slots.*.slot_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
                    'slots.*.max_patients_per_slot' => ['nullable', 'integer', 'min:1', 'max:100'],
                    'slots.*.consultation_fee'      => ['nullable', 'numeric', 'min:0'],
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'doctor_id'              => ['nullable', 'integer', 'exists:employees,id'],
                    'department_id'          => ['nullable', 'integer', 'exists:departments,id'],
                    'schedule_name'          => ['required', 'string', 'max:191'],
                    'effective_from'         => ['required', 'date'],
                    'effective_to'           => ['nullable', 'date', 'after_or_equal:effective_from'],
                    'slot_duration_minutes'  => ['required', 'integer', 'min:5', 'max:240'],
                    'max_patients_per_slot'  => ['required', 'integer', 'min:1', 'max:100'],
                    'consultation_fee'       => ['nullable', 'numeric', 'min:0'],
                    'status'                 => ['nullable', Rule::in(StatusEnum::getKeys())],
                    'remarks'                => ['nullable', 'string'],
                ];

            default:
                return [];
        }
    }

    public function messages()
    {
        $messages = parent::messages();
        $includesMessages = [
            'doctor_id.required'              => 'Doctor is required.',
            'doctor_id.exists'                => 'Selected doctor does not exist.',
            'department_id.exists'            => 'Selected department does not exist.',
            'schedule_name.required'          => 'Schedule name is required.',
            'effective_from.required'         => 'Effective from date is required.',
            'effective_from.date'             => 'Effective from must be a valid date.',
            'effective_to.after_or_equal'     => 'Effective to must be on or after effective from.',
            'slot_duration_minutes.required'  => 'Slot duration is required.',
            'slot_duration_minutes.min'       => 'Slot duration must be at least 5 minutes.',
            'max_patients_per_slot.required'  => 'Max patients per slot is required.',
        ];
        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $includesAttributes = [
            'doctor_id'             => 'Doctor',
            'department_id'         => 'Department',
            'schedule_name'         => 'Schedule Name',
            'effective_from'        => 'Effective From',
            'effective_to'          => 'Effective To',
            'slot_duration_minutes' => 'Slot Duration (min)',
            'max_patients_per_slot' => 'Max Patients / Slot',
            'consultation_fee'      => 'Consultation Fee',
        ];
        return array_merge($attributes, $includesAttributes);
    }
}