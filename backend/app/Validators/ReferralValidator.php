<?php

namespace App\Validators;

class ReferralValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id'               => ['required', 'integer', 'exists:opd_visits,id'],
            'patient_id'                 => ['required', 'integer', 'exists:patients,id'],
            'referring_doctor_id'        => ['required', 'integer', 'exists:employees,id'],
            'referred_to_department_id'  => ['nullable', 'integer', 'exists:departments,id'],
            'referred_to_doctor_id'      => ['nullable', 'integer', 'exists:employees,id'],
            'external_facility_name'     => ['nullable', 'string', 'max:200'],
            'reason'                     => ['required', 'string'],
            'urgency'                    => ['nullable', 'string', 'in:routine,urgent,emergency'],
            'referral_status'            => ['nullable', 'string', 'in:pending,accepted,completed,cancelled'],
            'notes'                      => ['nullable', 'string'],
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
            'reason.required' => 'Reason for referral is required.',
        ];
        return array_merge($messages, $extra);
    }
}
