<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class ErVisitValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'arrival_mode'    => ['nullable', Rule::in(['walk_in', 'ambulance', 'referred', 'police', 'other'])],
            'chief_complaint' => ['nullable', 'string'],
            'arrival_at'      => ['nullable', 'date'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['patient_id'] = ['required', 'integer', 'exists:patients,id'];
                $common['chief_complaint'] = ['required', 'string'];
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
            'patient_id.required'      => 'Patient is required.',
            'chief_complaint.required' => 'Chief complaint is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'patient_id'      => 'Patient',
            'chief_complaint' => 'Chief Complaint',
        ]);
    }
}
