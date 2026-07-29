<?php

namespace App\Validators;

class ShiftHandoverValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'role_type'      => ['nullable', 'string', 'in:doctor,nurse'],
            'ward_id'        => ['nullable', 'integer'],
            'from_user_id'   => ['nullable', 'integer'],
            'to_user_id'     => ['nullable', 'integer'],
            'shift_label'    => ['nullable', 'string', 'max:60'],
            'summary'        => ['nullable', 'string'],
            'items'          => ['nullable', 'array'],
            'state'          => ['nullable', 'string', 'max:20'],
            'handed_over_at' => ['nullable', 'date'],
            'status'         => ['nullable', 'integer'],
        ];

        switch ($this->request->method()) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
                return $common;
            default:
                return [];
        }
    }

    public function messages()
    {
        return parent::messages();
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
