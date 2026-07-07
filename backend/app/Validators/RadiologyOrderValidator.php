<?php

namespace App\Validators;

class RadiologyOrderValidator extends BaseValidator
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
            'ipd_admission_id'     => ['nullable', 'integer', 'exists:ipd_admissions,id'],
            'priority'             => ['nullable', 'in:routine,urgent,stat'],
            'clinical_indication'  => ['nullable', 'string'],
            'items'                => ['nullable', 'array'],
            'items.*.radiology_test_id' => ['required_with:items', 'integer', 'exists:radiology_tests,id'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['patient_id'] = ['required', 'integer', 'exists:patients,id'];
                $common['items'] = ['required', 'array', 'min:1'];
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

        return array_merge($messages, [
            'patient_id.required' => 'Patient is required.',
            'items.required'      => 'At least one study is required.',
            'items.min'           => 'At least one study is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'patient_id' => 'Patient',
            'items'      => 'Studies',
        ]);
    }
}
