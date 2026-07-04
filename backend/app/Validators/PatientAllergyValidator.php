<?php

namespace App\Validators;

class PatientAllergyValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return [
                    'patient_id'    => ['required', 'exists:patients,id'],
                    'allergy_type'  => ['required', 'in:drug,food,environmental,other'],
                    'allergen_name' => ['required', 'string', 'max:255'],
                    'drug_id'       => ['nullable', 'exists:items,id'],
                    'reaction_type' => ['nullable', 'string', 'max:255'],
                    'severity'      => ['required', 'in:mild,moderate,severe'],
                    'notes'         => ['nullable', 'string'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'patient_id'    => ['sometimes', 'required', 'exists:patients,id'],
                    'allergy_type'  => ['sometimes', 'required', 'in:drug,food,environmental,other'],
                    'allergen_name' => ['sometimes', 'required', 'string', 'max:255'],
                    'drug_id'       => ['nullable', 'exists:items,id'],
                    'reaction_type' => ['nullable', 'string', 'max:255'],
                    'severity'      => ['sometimes', 'required', 'in:mild,moderate,severe'],
                    'notes'         => ['nullable', 'string'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'patient_id.required'    => 'Patient is required.',
            'allergen_name.required' => 'Allergen name is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'patient_id'    => 'Patient',
            'allergen_name' => 'Allergen Name',
        ];

        return array_merge($attributes, $includesAttributes);
    }
}
