<?php

namespace App\Validators;

class CodeBlueEventValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'event_type'    => ['nullable', 'string', 'in:code_blue,rapid_response'],
            'patient_id'    => ['nullable', 'integer', 'exists:patients,id'],
            'ward_id'       => ['nullable', 'integer'],
            'bed_id'        => ['nullable', 'integer'],
            'location'      => ['nullable', 'string', 'max:150'],
            'state'         => ['nullable', 'string', 'max:20'],
            'severity'      => ['nullable', 'string', 'max:20'],
            'reason'        => ['nullable', 'string'],
            'responders'    => ['nullable', 'array'],
            'outcome_notes' => ['nullable', 'string'],
            'raised_by'     => ['nullable', 'integer'],
            'raised_at'     => ['nullable', 'date'],
            'responded_at'  => ['nullable', 'date'],
            'resolved_at'   => ['nullable', 'date'],
            'status'        => ['nullable', 'integer'],
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
