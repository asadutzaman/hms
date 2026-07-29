<?php

namespace App\Validators;

class SoapNoteValidator extends BaseValidator
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
            'opd_visit_id'     => ['nullable', 'integer'],
            'ipd_admission_id' => ['nullable', 'integer'],
            'author_user_id'   => ['nullable', 'integer', 'exists:users,id'],
            'subjective'       => ['nullable', 'string'],
            'objective'        => ['nullable', 'string'],
            'assessment'       => ['nullable', 'string'],
            'plan'             => ['nullable', 'string'],
            'noted_at'         => ['nullable', 'date'],
            'status'           => ['nullable', 'integer'],
        ];

        switch ($this->request->method()) {
            case 'POST':
                $common['patient_id'] = ['required', 'integer', 'exists:patients,id'];
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
            'patient_id.required' => 'Patient is required.',
            'patient_id.exists'   => 'Selected patient does not exist.',
        ]);
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
