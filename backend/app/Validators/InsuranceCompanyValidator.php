<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class InsuranceCompanyValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'tpa_type'       => ['nullable', Rule::in(['insurer', 'corporate'])],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string'],
            'credit_limit'   => ['nullable', 'numeric', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
            'description'    => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['code'] = ['required', 'string', 'max:30', 'unique:insurance_companies,code'];
                $common['name'] = ['required', 'string', 'max:255'];
                return $common;
            case 'PUT':
            case 'PATCH':
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
            'code.required' => 'Insurer code is required.',
            'code.unique'   => 'An insurance company with this code already exists.',
            'name.required' => 'Insurer name is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'code' => 'Code',
            'name' => 'Name',
        ]);
    }
}
