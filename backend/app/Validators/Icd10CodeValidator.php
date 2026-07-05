<?php

namespace App\Validators;

class Icd10CodeValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'code'        => ['required', 'string', 'max:20'],
            'description' => ['required', 'string', 'max:255'],
            'category'    => ['nullable', 'string', 'max:100'],
            'is_billable' => ['nullable', 'boolean'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['code'] = ['required', 'string', 'max:20', 'unique:icd10_codes,code'];
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
            'code.required'        => 'ICD-10 code is required.',
            'code.unique'          => 'This ICD-10 code already exists.',
            'description.required' => 'Description is required.',
        ];
        return array_merge($messages, $extra);
    }
}
