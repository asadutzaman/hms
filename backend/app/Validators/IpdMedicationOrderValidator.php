<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class IpdMedicationOrderValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'drug_id'         => ['nullable', 'integer'],
            'drug_name'       => ['nullable', 'string', 'max:255'],
            'dose_value'      => ['nullable', 'numeric', 'min:0'],
            'dose_unit'       => ['nullable', 'string', 'max:20'],
            'route'           => ['nullable', 'string', 'max:20'],
            'frequency'       => ['nullable', Rule::in(['OD', 'BD', 'TID', 'QID', 'HS', 'STAT', 'SOS', 'PRN'])],
            'duration_value'  => ['nullable', 'integer', 'min:1'],
            'duration_unit'   => ['nullable', 'string', 'in:days,weeks'],
            'is_prn'          => ['nullable', 'boolean'],
            'instruction'     => ['nullable', 'string'],
            'start_date'      => ['nullable', 'date'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['admission_id'] = ['required', 'integer', 'exists:ipd_admissions,id'];
                $common['drug_name'] = ['required', 'string', 'max:255'];
                $common['frequency'] = ['required', Rule::in(['OD', 'BD', 'TID', 'QID', 'HS', 'STAT', 'SOS', 'PRN'])];
                $common['start_date'] = ['required', 'date'];
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
            'drug_name.required' => 'Drug name is required.',
            'frequency.required' => 'Frequency is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'drug_name' => 'Drug Name',
            'dose_value' => 'Dose',
        ]);
    }
}
