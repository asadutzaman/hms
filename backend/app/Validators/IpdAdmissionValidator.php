<?php

namespace App\Validators;

class IpdAdmissionValidator extends BaseValidator
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
                    'patient_id'               => ['required', 'integer', 'exists:patients,id'],
                    'bed_id'                   => ['required', 'integer', 'exists:beds,id'],
                    'admission_type'           => ['required', 'in:emergency,planned'],
                    'opd_visit_id'             => ['nullable', 'integer', 'exists:opd_visits,id'],
                    'attending_doctor_id'      => ['nullable', 'integer', 'exists:employees,id'],
                    'department_id'            => ['nullable', 'integer'],
                    'admission_date'           => ['nullable', 'date'],
                    'expected_discharge_date'  => ['nullable', 'date'],
                    'diagnosis_at_admission'   => ['nullable', 'string'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'attending_doctor_id'      => ['nullable', 'integer', 'exists:employees,id'],
                    'department_id'            => ['nullable', 'integer'],
                    'expected_discharge_date'  => ['nullable', 'date'],
                    'diagnosis_at_admission'   => ['nullable', 'string'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'patient_id.required'     => 'Patient is required.',
            'bed_id.required'         => 'Bed is required.',
            'admission_type.required' => 'Admission type is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'patient_id'     => 'Patient',
            'bed_id'         => 'Bed',
            'admission_type' => 'Admission Type',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
