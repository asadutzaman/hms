<?php

namespace App\Validators;

class WardValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return [
                    'name'      => ['required'],
                    'branch_id' => ['nullable', 'integer'],
                    'ward_type' => ['nullable', 'in:general,semi_private,private,icu,emergency'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name'      => ['required'],
                    'branch_id' => ['nullable', 'integer'],
                    'ward_type' => ['nullable', 'in:general,semi_private,private,icu,emergency'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'name.required' => 'Name is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'name' => 'Name',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
