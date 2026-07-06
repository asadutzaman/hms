<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class IpdDischargeSummaryValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'discharge_diagnosis'     => ['nullable', 'string'],
            'hospital_course'         => ['nullable', 'string'],
            'procedures_performed'    => ['nullable', 'string'],
            'discharge_condition'     => ['nullable', Rule::in(['stable', 'improved', 'unchanged', 'deteriorated'])],
            'follow_up_instructions'  => ['nullable', 'string'],
            'discharge_advice'        => ['nullable', 'string'],
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
            'discharge_condition' => 'Discharge Condition',
        ]);
    }
}
