<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class OpdInvestigationOrderItemValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_investigation_order_id' => ['nullable', 'integer', 'exists:opd_investigation_orders,id'],
            'lab_test_id'                => ['nullable', 'integer', 'exists:lab_tests,id'],
            'test_name'                  => ['nullable', 'string', 'max:200'],
            'test_code'                  => ['nullable', 'string', 'max:50'],
            'department'                 => ['nullable', 'string', 'max:100'],
            'sample_type'                => ['nullable', 'string', 'max:100'],
            'unit_price'                 => ['nullable', 'numeric', 'min:0'],
            'amount'                     => ['nullable', 'numeric', 'min:0'],
            'tat_minutes'                => ['nullable', 'integer', 'min:0'],
            'sequence'                   => ['nullable', 'integer', 'min:0'],
            'result_status'              => ['nullable', Rule::in(['pending', 'partial', 'final', 'amended', 'corrected', 'cancelled'])],
            'result_value'               => ['nullable', 'string', 'max:2000'],
            'result_remarks'             => ['nullable', 'string', 'max:2000'],
            'resulted_at'                => ['nullable', 'date'],
            'resulted_by'                => ['nullable', 'integer', 'exists:users,id'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_investigation_order_id'] = ['required', 'integer', 'exists:opd_investigation_orders,id'];
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
            'opd_investigation_order_id.required' => 'Investigation order is required.',
            'opd_investigation_order_id.exists'   => 'Selected investigation order does not exist.',
            'result_status.in'                    => 'Result status must be one of: pending, partial, final, amended, corrected, cancelled.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_investigation_order_id' => 'Investigation Order',
            'lab_test_id'                => 'Lab Test',
            'test_name'                  => 'Test Name',
            'test_code'                  => 'Test Code',
            'unit_price'                 => 'Unit Price',
            'tat_minutes'                => 'TAT (minutes)',
            'result_status'              => 'Result Status',
            'result_value'               => 'Result Value',
            'result_remarks'             => 'Result Remarks',
            'resulted_at'                => 'Resulted At',
            'resulted_by'                => 'Resulted By',
        ];
        return array_merge($attributes, $extra);
    }
}
