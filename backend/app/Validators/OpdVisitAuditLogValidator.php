<?php

namespace App\Validators;

use App\Enums\OpdVisitStatusEnum;
use Illuminate\Validation\Rule;

class OpdVisitAuditLogValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id'  => ['nullable', 'integer', 'exists:opd_visits,id'],
            'action'        => ['nullable', Rule::in(['create', 'status_change', 'update', 'cancel', 'close', 'bill_generated', 'payment_recorded'])],
            'from_status'   => ['nullable', Rule::in(OpdVisitStatusEnum::getKeys())],
            'to_status'     => ['nullable', Rule::in(OpdVisitStatusEnum::getKeys())],
            'actor_id'      => ['nullable', 'integer', 'exists:users,id'],
            'actor_type'    => ['nullable', 'string', 'max:50'],
            'meta'          => ['nullable', 'array'],
            'remarks'       => ['nullable', 'string', 'max:1000'],
            'occurred_at'   => ['nullable', 'date'],
            'ip_address'    => ['nullable', 'ip'],
            'user_agent'    => ['nullable', 'string', 'max:500'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_visit_id'] = ['required', 'integer', 'exists:opd_visits,id'];
                $common['action'] = ['required', Rule::in(['create', 'status_change', 'update', 'cancel', 'close', 'bill_generated', 'payment_recorded'])];
                $common['occurred_at'] = ['required', 'date'];
                $common['from_status'] = ['required_if:action,status_change', 'nullable', Rule::in(OpdVisitStatusEnum::getKeys())];
                $common['to_status'] = ['required_if:action,status_change', 'nullable', Rule::in(OpdVisitStatusEnum::getKeys())];
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
        $extra = [
            'opd_visit_id.required'   => 'OPD visit is required.',
            'opd_visit_id.exists'     => 'Selected OPD visit does not exist.',
            'action.required'         => 'Action is required.',
            'action.in'               => 'Action must be one of: create, status_change, update, cancel, close, bill_generated, payment_recorded.',
            'from_status.required_if' => 'From-status is required when action is status_change.',
            'to_status.required_if'   => 'To-status is required when action is status_change.',
            'from_status.in'          => 'Invalid from-status.',
            'to_status.in'            => 'Invalid to-status.',
            'occurred_at.required'    => 'Occurred-at is required.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_visit_id' => 'OPD Visit',
            'actor_id'     => 'Actor',
            'actor_type'   => 'Actor Type',
            'from_status'  => 'From Status',
            'to_status'    => 'To Status',
            'occurred_at'  => 'Occurred At',
            'ip_address'   => 'IP Address',
            'user_agent'   => 'User Agent',
        ];
        return array_merge($attributes, $extra);
    }
}
