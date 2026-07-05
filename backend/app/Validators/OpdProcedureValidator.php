<?php

namespace App\Validators;

class OpdProcedureValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id'   => ['required', 'integer', 'exists:opd_visits,id'],
            'patient_id'     => ['required', 'integer', 'exists:patients,id'],
            'procedure_name' => ['required', 'string', 'max:200'],
            'procedure_code' => ['nullable', 'string', 'max:50'],
            'performed_by'   => ['required', 'integer', 'exists:employees,id'],
            'performed_at'   => ['required', 'date'],
            'notes'          => ['nullable', 'string'],
            'outcome'        => ['nullable', 'string', 'max:200'],
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
        $extra = [
            'procedure_name.required' => 'Procedure name is required.',
        ];
        return array_merge($messages, $extra);
    }
}
