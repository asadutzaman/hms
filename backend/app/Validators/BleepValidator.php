<?php

namespace App\Validators;

class BleepValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'from_user_id'    => ['nullable', 'integer'],
            'to_user_id'      => ['nullable', 'integer'],
            'patient_id'      => ['nullable', 'integer'],
            'ward_id'         => ['nullable', 'integer'],
            'callback'        => ['nullable', 'string', 'max:60'],
            'priority'        => ['nullable', 'string', 'in:routine,urgent,crash'],
            'message'         => ['nullable', 'string'],
            'state'           => ['nullable', 'string', 'max:20'],
            'acknowledged_at' => ['nullable', 'date'],
            'escalated_at'    => ['nullable', 'date'],
            'status'          => ['nullable', 'integer'],
        ];

        switch ($this->request->method()) {
            case 'POST':
                $common['message'] = ['required', 'string'];
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
        return array_merge(parent::messages(), [
            'message.required' => 'Message is required.',
        ]);
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
