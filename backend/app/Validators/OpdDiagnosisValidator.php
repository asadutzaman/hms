<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class OpdDiagnosisValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id'         => ['nullable', 'integer', 'exists:opd_visits,id'],
            'patient_id'           => ['nullable', 'integer', 'exists:patients,id'],
            'diagnosis_type'       => ['nullable', Rule::in(['primary', 'secondary', 'differential', 'final'])],
            'icd10_id'             => ['nullable', 'integer', 'exists:icd10_codes,id'],
            'icd10_code'           => ['nullable', 'string', 'max:20', 'regex:/^[A-Z][0-9]{2}(\.[0-9A-Z]{1,4})?$/'],
            'icd10_description'    => ['nullable', 'string', 'max:500'],
            'diagnosis_name'       => ['required', 'string', 'max:255'],
            'is_primary'           => ['nullable', 'boolean'],
            'is_chronic'           => ['nullable', 'boolean'],
            'is_confirmed'         => ['nullable', 'boolean'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'sequence'             => ['nullable', 'integer', 'min:1', 'max:999'],
            'recorded_by'          => ['nullable', 'integer', 'exists:users,id'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_visit_id'] = ['required', 'integer', 'exists:opd_visits,id'];
                return $common;

            case 'PUT':
            case 'PATCH':
                return $common;

            default:
                return [];
        }
    }

    public function messages()
    {
        $messages = parent::messages();
        $extra = [
            'opd_visit_id.required'    => 'OPD visit is required.',
            'opd_visit_id.exists'      => 'Selected OPD visit does not exist.',
            'diagnosis_name.required'  => 'Diagnosis name is required.',
            'icd10_code.regex'         => 'ICD-10 code must follow format like A00, A12.3, or S12.34X1.',
            'diagnosis_type.in'        => 'Diagnosis type must be one of: primary, secondary, differential, final.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_visit_id'      => 'OPD Visit',
            'patient_id'        => 'Patient',
            'diagnosis_type'    => 'Diagnosis Type',
            'icd10_code'        => 'ICD-10 Code',
            'icd10_description' => 'ICD-10 Description',
            'diagnosis_name'    => 'Diagnosis Name',
            'recorded_by'       => 'Recorded By',
        ];
        return array_merge($attributes, $extra);
    }
}
