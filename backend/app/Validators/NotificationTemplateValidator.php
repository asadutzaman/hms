<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class NotificationTemplateValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'name'             => ['nullable', 'string', 'max:255'],
            'channel'          => ['nullable', Rule::in(['in_app', 'email', 'sms'])],
            'subject_template' => ['nullable', 'string', 'max:255'],
            'body_template'    => ['nullable', 'string'],
            'is_active'        => ['nullable', 'boolean'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['key'] = ['required', 'string', 'max:60', 'unique:notification_templates,key'];
                $common['name'] = ['required', 'string', 'max:255'];
                $common['channel'] = ['required', Rule::in(['in_app', 'email', 'sms'])];
                $common['body_template'] = ['required', 'string'];
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
            'key.required'  => 'Template key is required.',
            'key.unique'    => 'A template with this key already exists.',
            'name.required' => 'Name is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'key'           => 'Key',
            'body_template' => 'Body Template',
        ]);
    }
}
