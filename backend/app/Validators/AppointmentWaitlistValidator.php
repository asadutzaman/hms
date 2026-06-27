<?php

namespace App\Validators;

use App\Enums\WaitlistStatusEnum;
use Illuminate\Validation\Rule;

class AppointmentWaitlistValidator extends BaseValidator
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
                    'preferred_date_from'  => ['required', 'date'],
                    'preferred_date_to'    => ['nullable', 'date', 'after_or_equal:preferred_date_from'],
                    'preferred_time_slot'  => ['nullable', 'string', 'max:50'],
                    'priority'             => ['nullable', 'integer', 'min:1', 'max:10'],
                    'status'               => ['nullable', Rule::in(WaitlistStatusEnum::getKeys())],
                    'notes'                => ['nullable', 'string', 'max:500'],
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'patient_id'           => ['nullable', 'integer', 'exists:patients,id'],
                    'doctor_id'            => ['nullable', 'integer', 'exists:employees,id'],
                    'preferred_date_from'  => ['required', 'date'],
                    'preferred_date_to'    => ['nullable', 'date', 'after_or_equal:preferred_date_from'],
                    'preferred_time_slot'  => ['nullable', 'string', 'max:50'],
                    'priority'             => ['nullable', 'integer', 'min:1', 'max:10'],
                    'status'               => ['nullable', Rule::in(WaitlistStatusEnum::getKeys())],
                    'notes'                => ['nullable', 'string', 'max:500'],
                ];

            default:
                return [];
        }
    }

    public function messages()
    {
        $messages = parent::messages();
        $includesMessages = [
            'patient_id.required'           => 'Patient is required.',
            'doctor_id.required'            => 'Doctor is required.',
            'preferred_date_from.required'  => 'Preferred start date is required.',
            'preferred_date_to.after_or_equal' => 'Preferred end date must be on or after the start date.',
        ];
        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $includesAttributes = [
            'patient_id'          => 'Patient',
            'doctor_id'           => 'Doctor',
            'preferred_date_from' => 'Preferred Date From',
            'preferred_date_to'   => 'Preferred Date To',
            'preferred_time_slot' => 'Preferred Time Slot',
        ];
        return array_merge($attributes, $includesAttributes);
    }
}