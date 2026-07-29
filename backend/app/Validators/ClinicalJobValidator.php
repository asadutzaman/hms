<?php

namespace App\Validators;

class ClinicalJobValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'title'        => ['nullable', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'job_type'     => ['nullable', 'string', 'max:40'],
            'priority'     => ['nullable', 'string', 'in:routine,urgent,critical'],
            'patient_id'   => ['nullable', 'integer'],
            'ward_id'      => ['nullable', 'integer'],
            'bed_id'       => ['nullable', 'integer'],
            'requested_by' => ['nullable', 'integer'],
            'assigned_to'  => ['nullable', 'integer'],
            'role_type'    => ['nullable', 'string', 'in:doctor,nurse'],
            'state'        => ['nullable', 'string', 'max:20'],
            'due_at'       => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'status'       => ['nullable', 'integer'],
        ];

        switch ($this->request->method()) {
            case 'POST':
                $common['title'] = ['required', 'string', 'max:200'];
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
            'title.required' => 'Task title is required.',
        ]);
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
