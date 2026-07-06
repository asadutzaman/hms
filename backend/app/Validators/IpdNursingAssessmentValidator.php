<?php

namespace App\Validators;

class IpdNursingAssessmentValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'general_appearance'         => ['nullable', 'string'],
            'mobility_status'            => ['nullable', 'string', 'max:50'],
            'fall_risk_score'            => ['nullable', 'integer', 'min:0', 'max:125'],
            'pressure_injury_risk_score' => ['nullable', 'integer', 'min:0', 'max:23'],
            'pain_assessment'            => ['nullable', 'string'],
            'nutrition_risk'             => ['nullable', 'string'],
            'skin_integrity_notes'       => ['nullable', 'string'],
            'psychosocial_notes'         => ['nullable', 'string'],
            'care_plan_notes'            => ['nullable', 'string'],
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

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'fall_risk_score'            => 'Fall Risk Score (Morse Scale, 0-125)',
            'pressure_injury_risk_score' => 'Pressure Injury Risk Score (Braden Scale, 0-23)',
        ]);
    }
}
