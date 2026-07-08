<?php

namespace App\Validators;

class OtBookingValidator extends BaseValidator
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
                    'patient_id'           => ['required', 'integer', 'exists:patients,id'],
                    'ipd_admission_id'     => ['nullable', 'integer', 'exists:ipd_admissions,id'],
                    'theatre_id'           => ['required', 'integer', 'exists:theatres,id'],
                    'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
                    'surgeon_id'           => ['required', 'integer', 'exists:employees,id'],
                    'anaesthetist_id'      => ['nullable', 'integer', 'exists:employees,id'],
                    'surgery_name'         => ['required', 'string', 'max:255'],
                    'surgery_type'         => ['nullable', 'in:elective,emergency'],
                    'scheduled_date'       => ['required', 'date'],
                    'scheduled_start_time' => ['required', 'date_format:H:i'],
                    'scheduled_end_time'   => ['required', 'date_format:H:i', 'after:scheduled_start_time'],
                    'equipment_list'       => ['nullable', 'array'],
                    'notes'                => ['nullable', 'string'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'theatre_id'           => ['nullable', 'integer', 'exists:theatres,id'],
                    'scheduled_date'       => ['nullable', 'date'],
                    'scheduled_start_time' => ['nullable', 'date_format:H:i'],
                    'scheduled_end_time'   => ['nullable', 'date_format:H:i'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        return parent::messages();
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
