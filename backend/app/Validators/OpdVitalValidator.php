<?php

namespace App\Validators;

class OpdVitalValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id'         => ['nullable', 'integer', 'exists:opd_visits,id'],
            'patient_id'           => ['nullable', 'integer', 'exists:patients,id'],
            'recorded_by'          => ['nullable', 'integer', 'exists:users,id'],
            'recorded_at'          => ['nullable', 'date'],
            'bp_systolic'          => ['nullable', 'integer', 'min:50', 'max:300'],
            'bp_diastolic'         => ['nullable', 'integer', 'min:30', 'max:200', 'lte:bp_systolic'],
            'pulse_bpm'            => ['nullable', 'integer', 'min:20', 'max:250'],
            'temperature_c'        => ['nullable', 'numeric', 'min:25.0', 'max:45.0'],
            'temperature_method'   => ['nullable', 'string', 'max:30'],
            'spo2_pct'             => ['nullable', 'integer', 'min:50', 'max:100'],
            'respiratory_rate'     => ['nullable', 'integer', 'min:5', 'max:80'],
            'weight_kg'            => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'height_cm'            => ['nullable', 'numeric', 'min:20', 'max:260'],
            'bmi'                  => ['nullable', 'numeric', 'min:5', 'max:100'],
            'blood_glucose_mg_dl'  => ['nullable', 'numeric', 'min:20', 'max:900'],
            'pain_score'           => ['nullable', 'integer', 'min:0', 'max:10'],
            'notes'                => ['nullable', 'string', 'max:2000'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_visit_id'] = ['required', 'integer', 'exists:opd_visits,id'];
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
        $extra = [
            'opd_visit_id.required' => 'OPD visit is required.',
            'opd_visit_id.exists'   => 'Selected OPD visit does not exist.',
            'bp_diastolic.lte'      => 'Diastolic BP must be less than or equal to systolic BP.',
            'pain_score.min'        => 'Pain score must be between 0 and 10.',
            'pain_score.max'        => 'Pain score must be between 0 and 10.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_visit_id'        => 'OPD Visit',
            'patient_id'          => 'Patient',
            'bp_systolic'         => 'Systolic BP',
            'bp_diastolic'        => 'Diastolic BP',
            'pulse_bpm'           => 'Pulse',
            'temperature_c'       => 'Temperature',
            'spo2_pct'            => 'SpO2',
            'respiratory_rate'    => 'Respiratory Rate',
            'weight_kg'           => 'Weight',
            'height_cm'           => 'Height',
            'bmi'                 => 'BMI',
            'blood_glucose_mg_dl' => 'Blood Glucose',
            'pain_score'          => 'Pain Score',
        ];
        return array_merge($attributes, $extra);
    }
}
