<?php

namespace App\Validators;

class IpdVitalValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'admission_id'        => ['nullable', 'integer', 'exists:ipd_admissions,id'],
            'recorded_at'         => ['nullable', 'date'],
            'bp_systolic'         => ['nullable', 'integer', 'between:50,300'],
            'bp_diastolic'        => ['nullable', 'integer', 'between:30,200', 'lte:bp_systolic'],
            'pulse_bpm'           => ['nullable', 'integer', 'between:20,250'],
            'temperature_c'       => ['nullable', 'numeric', 'between:25,45'],
            'temperature_method'  => ['nullable', 'string', 'max:20'],
            'spo2_pct'            => ['nullable', 'integer', 'between:50,100'],
            'respiratory_rate'    => ['nullable', 'integer', 'between:4,60'],
            'weight_kg'           => ['nullable', 'numeric', 'between:0.5,500'],
            'height_cm'           => ['nullable', 'numeric', 'between:20,260'],
            'blood_glucose_mg_dl' => ['nullable', 'numeric', 'between:0,1000'],
            'pain_score'          => ['nullable', 'integer', 'between:0,10'],
            'notes'               => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['admission_id'] = ['required', 'integer', 'exists:ipd_admissions,id'];
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
            'bp_diastolic.lte' => 'Diastolic BP cannot exceed systolic BP.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'bp_systolic'  => 'Systolic BP',
            'bp_diastolic' => 'Diastolic BP',
            'pulse_bpm'    => 'Pulse',
            'spo2_pct'     => 'SpO2',
        ]);
    }
}
