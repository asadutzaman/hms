<?php

namespace App\Validators;

class AtoeAssessmentValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'patient_id'       => ['nullable', 'integer', 'exists:patients,id'],
            'ipd_admission_id' => ['nullable', 'integer'],
            'assessed_by'      => ['nullable', 'integer'],
            'assessed_at'      => ['nullable', 'date'],
            'airway'           => ['nullable', 'string'],
            'breathing'        => ['nullable', 'string'],
            'circulation'      => ['nullable', 'string'],
            'disability'       => ['nullable', 'string'],
            'exposure'         => ['nullable', 'string'],
            'news2_score'      => ['nullable', 'integer', 'min:0', 'max:30'],
            'impression'       => ['nullable', 'string'],
            'plan'             => ['nullable', 'string'],
            'status'           => ['nullable', 'integer'],
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
