<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class PatientAttachmentValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'category'    => ['nullable', Rule::in(['lab_report', 'prescription', 'scan', 'photo', 'id_document', 'insurance', 'other'])],
            'title'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['patient_id'] = ['required', 'integer', 'exists:patients,id'];
                $common['file'] = ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,csv'];
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
            'patient_id.required' => 'Patient is required.',
            'file.required'       => 'A file is required.',
            'file.max'            => 'File must not exceed 20 MB.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'patient_id' => 'Patient',
        ]);
    }
}
