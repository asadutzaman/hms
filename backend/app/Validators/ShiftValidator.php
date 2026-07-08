<?php

namespace App\Validators;

class ShiftValidator extends BaseValidator
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
                    'name'       => ['required'],
                    'start_time' => ['required', 'date_format:H:i'],
                    'end_time'   => ['required', 'date_format:H:i'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name'       => ['required'],
                    'start_time' => ['nullable', 'date_format:H:i'],
                    'end_time'   => ['nullable', 'date_format:H:i'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'name.required'   => 'Name is required.',
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
