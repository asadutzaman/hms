<?php

namespace App\Validators;

class LabTestValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'category'      => ['nullable', 'string', 'max:100'],
            'sample_type'   => ['nullable', 'string', 'max:50'],
            'tat_hours'     => ['nullable', 'integer', 'min:0'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
            'description'   => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['code'] = ['required', 'string', 'max:30', 'unique:lab_tests,code'];
                $common['name'] = ['required', 'string', 'max:255'];
                return $common;
            case 'PUT':
            case 'PATCH':
                $common['name'] = ['nullable', 'string', 'max:255'];
                return $common;
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        return array_merge($messages, [
            'code.required' => 'Test code is required.',
            'code.unique'   => 'A test with this code already exists.',
            'name.required' => 'Test name is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'code'          => 'Test Code',
            'name'          => 'Test Name',
            'sample_type'   => 'Sample Type',
            'tat_hours'     => 'Turnaround Time (hours)',
            'default_price' => 'Default Price',
        ]);
    }
}
