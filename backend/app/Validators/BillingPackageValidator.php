<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class BillingPackageValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'package_type' => ['nullable', Rule::in(['opd', 'ipd', 'both'])],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['nullable', 'boolean'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['code'] = ['required', 'string', 'max:30', 'unique:billing_packages,code'];
                $common['name'] = ['required', 'string', 'max:255'];
                $common['fixed_price'] = ['required', 'numeric', 'min:0'];
                return $common;
            case 'PUT':
            case 'PATCH':
                $common['name'] = ['nullable', 'string', 'max:255'];
                $common['fixed_price'] = ['nullable', 'numeric', 'min:0'];
                return $common;
            default:
                return [];
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        return array_merge($messages, [
            'code.required'        => 'Package code is required.',
            'code.unique'          => 'A package with this code already exists.',
            'name.required'        => 'Package name is required.',
            'fixed_price.required' => 'Fixed price is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'code'        => 'Code',
            'name'        => 'Name',
            'fixed_price' => 'Fixed Price',
        ]);
    }
}
