<?php

namespace App\Validators;

class BedValidator extends BaseValidator
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
                    'ward_id'    => ['required', 'integer'],
                    'bed_number' => ['required', 'string'],
                    'daily_rate' => ['nullable', 'numeric', 'min:0'],
                    'bed_status' => ['nullable', 'in:vacant,occupied,reserved,cleaning,maintenance'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'ward_id'    => ['required', 'integer'],
                    'bed_number' => ['required', 'string'],
                    'daily_rate' => ['nullable', 'numeric', 'min:0'],
                    'bed_status' => ['nullable', 'in:vacant,occupied,reserved,cleaning,maintenance'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'ward_id.required'    => 'Ward is required.',
            'bed_number.required' => 'Bed number is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'ward_id'    => 'Ward',
            'bed_number' => 'Bed Number',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
