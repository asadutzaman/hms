<?php

namespace App\Validators;

use App\Enums\OpdInvestigationOrderStatusEnum;
use Illuminate\Validation\Rule;

class OpdInvestigationOrderValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id'         => ['nullable', 'integer', 'exists:opd_visits,id'],
            'patient_id'           => ['nullable', 'integer', 'exists:patients,id'],
            'order_no'             => ['nullable', 'string', 'max:50'],
            'priority'             => ['nullable', Rule::in(['routine', 'urgent', 'stat'])],
            'ordered_by'           => ['nullable', 'integer', 'exists:users,id'],
            'ordered_at'           => ['nullable', 'date'],
            'clinical_indication'  => ['nullable', 'string', 'max:2000'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'status'               => ['nullable', Rule::in(OpdInvestigationOrderStatusEnum::getKeys())],
            'items'                => ['nullable', 'array', 'min:1'],
            'items.*.lab_test_id'  => ['nullable', 'integer', 'exists:lab_tests,id'],
            'items.*.test_code'    => ['nullable', 'string', 'max:50'],
            'items.*.test_name'    => ['required_with:items', 'string', 'max:200'],
            'items.*.amount'       => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price'   => ['nullable', 'numeric', 'min:0'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_visit_id'] = ['required', 'integer', 'exists:opd_visits,id'];
                $common['items'] = ['required', 'array', 'min:1'];
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
            'opd_visit_id.required'         => 'OPD visit is required.',
            'opd_visit_id.exists'           => 'Selected OPD visit does not exist.',
            'items.required'                => 'At least one investigation item is required.',
            'items.min'                     => 'At least one investigation item is required.',
            'items.*.test_name.required_with' => 'Test name is required for each item.',
            'priority.in'                   => 'Priority must be one of: routine, urgent, stat.',
            'status.in'                     => 'Invalid status.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_visit_id'        => 'OPD Visit',
            'patient_id'          => 'Patient',
            'order_no'            => 'Order Number',
            'ordered_by'          => 'Orderer',
            'ordered_at'          => 'Ordered At',
            'clinical_indication' => 'Clinical Indication',
        ];
        return array_merge($attributes, $extra);
    }
}
