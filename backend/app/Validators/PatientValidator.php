<?php

namespace App\Validators;

class PatientValidator extends BaseValidator
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
                    'mrn' => ['required'],
                    'first_name' => ['required'],
                    'last_name' => ['required'],
                    'date_of_birth' => ['required'],
                    'gender' => ['required'],
                    'blood_group' => ['required'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'mrn' => ['required'],
                    'first_name' => ['required'],
                    'last_name' => ['required'],
                    'date_of_birth' => ['required'],
                    'gender' => ['required'],
                    'blood_group' => ['required'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'mrn.required' => 'MRN is required.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'gender.required' => 'Gender is required.',
            'blood_group.required' => 'Blood group is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'mrn' => 'MRN',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'blood_group' => 'Blood Group',
        ];

        return array_merge($attributes, $includesAttributes);
    }
}
