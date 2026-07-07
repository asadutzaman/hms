<?php

namespace App\Validators;

class PreAuthorizationValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'ipd_admission_id' => ['nullable', 'integer', 'exists:ipd_admissions,id'],
            'opd_visit_id'     => ['nullable', 'integer', 'exists:opd_visits,id'],
            'policy_number'    => ['nullable', 'string', 'max:255'],
            'diagnosis'        => ['nullable', 'string'],
            'treatment_plan'   => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['patient_id'] = ['required', 'integer', 'exists:patients,id'];
                $common['insurance_company_id'] = ['required', 'integer', 'exists:insurance_companies,id'];
                $common['insurance_scheme_id'] = ['nullable', 'integer', 'exists:insurance_schemes,id'];
                $common['estimated_amount'] = ['required', 'numeric', 'min:0'];
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
            'patient_id.required'           => 'Patient is required.',
            'insurance_company_id.required' => 'Insurance company is required.',
            'estimated_amount.required'     => 'Estimated amount is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'patient_id'           => 'Patient',
            'insurance_company_id' => 'Insurance Company',
            'estimated_amount'     => 'Estimated Amount',
        ]);
    }
}
