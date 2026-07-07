<?php

namespace App\Validators;

class InsuranceSchemeValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'coverage_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_limit'        => ['nullable', 'numeric', 'min:0'],
            'covered_services' => ['nullable', 'string'],
            'is_active'        => ['nullable', 'boolean'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['insurance_company_id'] = ['required', 'integer', 'exists:insurance_companies,id'];
                $common['name'] = ['required', 'string', 'max:255'];
                return $common;
            case 'PUT':
            case 'PATCH':
                $common['insurance_company_id'] = ['nullable', 'integer', 'exists:insurance_companies,id'];
                $common['name'] = ['nullable', 'string', 'max:255'];
                return $common;
            default:
                return [];
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        return array_merge($messages, [
            'insurance_company_id.required' => 'Insurance company is required.',
            'name.required'                 => 'Scheme name is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'insurance_company_id' => 'Insurance Company',
            'name'                 => 'Scheme Name',
        ]);
    }
}
