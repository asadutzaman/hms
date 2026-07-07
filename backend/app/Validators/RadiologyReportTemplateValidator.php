<?php

namespace App\Validators;

class RadiologyReportTemplateValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'modality'             => ['nullable', 'string', 'max:20'],
            'body_part'            => ['nullable', 'string', 'max:255'],
            'findings_template'    => ['nullable', 'string'],
            'impression_template'  => ['nullable', 'string'],
            'is_active'            => ['nullable', 'boolean'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
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
            'name.required' => 'Template name is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'name' => 'Template Name',
        ]);
    }
}
