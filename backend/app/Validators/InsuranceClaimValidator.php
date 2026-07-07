<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class InsuranceClaimValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'insurance_scheme_id'  => ['nullable', 'integer', 'exists:insurance_schemes,id'],
            'pre_authorization_id' => ['nullable', 'integer', 'exists:pre_authorizations,id'],
            'policy_number'        => ['nullable', 'string', 'max:255'],
            'notes'                => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['patient_id'] = ['required', 'integer', 'exists:patients,id'];
                $common['insurance_company_id'] = ['required', 'integer', 'exists:insurance_companies,id'];
                $common['billable_type'] = ['required', Rule::in(['opd_bill', 'ipd_bill'])];
                $common['billable_id'] = ['required', 'integer'];
                $common['claimed_amount'] = ['required', 'numeric', 'min:0'];
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
            'billable_type.required'        => 'Bill type is required.',
            'billable_id.required'          => 'Bill is required.',
            'claimed_amount.required'       => 'Claimed amount is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'patient_id'           => 'Patient',
            'insurance_company_id' => 'Insurance Company',
            'claimed_amount'       => 'Claimed Amount',
        ]);
    }
}
