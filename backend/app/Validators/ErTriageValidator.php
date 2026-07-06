<?php

namespace App\Validators;

class ErTriageValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'bp_systolic'      => ['nullable', 'integer', 'between:50,300'],
            'bp_diastolic'     => ['nullable', 'integer', 'between:30,200'],
            'pulse_bpm'        => ['nullable', 'integer', 'between:20,250'],
            'temperature_c'    => ['nullable', 'numeric', 'between:25,45'],
            'spo2_pct'         => ['nullable', 'integer', 'between:50,100'],
            'respiratory_rate' => ['nullable', 'integer', 'between:4,60'],
            'notes'            => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['er_visit_id'] = ['required', 'integer', 'exists:er_visits,id'];
                $common['triage_level'] = ['required', 'integer', 'between:1,5'];
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
            'er_visit_id.required'  => 'ER visit is required.',
            'triage_level.required' => 'Triage level is required.',
            'triage_level.between'  => 'Triage level must be between 1 and 5.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'er_visit_id'  => 'ER Visit',
            'triage_level' => 'Triage Level',
        ]);
    }
}
