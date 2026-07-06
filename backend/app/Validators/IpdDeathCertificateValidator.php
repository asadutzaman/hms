<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class IpdDeathCertificateValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'date_of_death'                 => ['nullable', 'date'],
            'time_of_death'                 => ['nullable', 'string'],
            'immediate_cause'                => ['nullable', 'string'],
            'antecedent_cause'               => ['nullable', 'string'],
            'underlying_cause'               => ['nullable', 'string'],
            'other_significant_conditions'   => ['nullable', 'string'],
            'manner_of_death'                => ['nullable', Rule::in(['natural', 'accident', 'suicide', 'homicide', 'undetermined'])],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['admission_id'] = ['required', 'integer', 'exists:ipd_admissions,id'];
                $common['date_of_death'] = ['required', 'date'];
                $common['immediate_cause'] = ['required', 'string'];
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
            'date_of_death.required'   => 'Date of death is required.',
            'immediate_cause.required' => 'Immediate cause of death is required.',
        ]);
    }
}
