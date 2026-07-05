<?php

namespace App\Validators;

class DrugInteractionValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'drug_a_id'      => ['required', 'integer', 'exists:drugs,id', 'different:drug_b_id'],
            'drug_b_id'      => ['required', 'integer', 'exists:drugs,id'],
            'severity'       => ['required', 'string', 'in:minor,moderate,severe,contraindicated'],
            'description'    => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

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
        $messages = parent::messages();

        $includesMessages = [
            'drug_a_id.different' => 'A drug cannot interact with itself.',
            'severity.in'         => 'Severity must be one of: minor, moderate, severe, contraindicated.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'drug_a_id' => 'Drug A',
            'drug_b_id' => 'Drug B',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
