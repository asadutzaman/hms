<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class RadiologyTestValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'modality'      => ['nullable', Rule::in(['xray', 'ct', 'mri', 'ultrasound', 'mammography', 'fluoroscopy', 'other'])],
            'body_part'     => ['nullable', 'string', 'max:255'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'tat_hours'     => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
            'description'   => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['code'] = ['required', 'string', 'max:30', 'unique:radiology_tests,code'];
                $common['name'] = ['required', 'string', 'max:255'];
                $common['modality'] = ['required', Rule::in(['xray', 'ct', 'mri', 'ultrasound', 'mammography', 'fluoroscopy', 'other'])];
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
            'code.required'     => 'Test code is required.',
            'code.unique'       => 'A radiology test with this code already exists.',
            'name.required'     => 'Test name is required.',
            'modality.required' => 'Modality is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'code'     => 'Code',
            'name'     => 'Name',
            'modality' => 'Modality',
        ]);
    }
}
