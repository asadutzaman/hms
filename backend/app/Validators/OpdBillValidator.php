<?php

namespace App\Validators;

use App\Enums\OpdBillStatusEnum;
use Illuminate\Validation\Rule;

class OpdBillValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id' => ['nullable', 'integer', 'exists:opd_visits,id'],
            'bill_no'      => ['nullable', 'string', 'max:50'],
            'bill_date'    => ['nullable', 'date'],
            'subtotal'     => ['nullable', 'numeric', 'min:0'],
            'discount'     => ['nullable', 'numeric', 'min:0', 'lte:subtotal'],
            'tax'          => ['nullable', 'numeric', 'min:0'],
            'total'        => ['nullable', 'numeric', 'min:0'],
            'paid'         => ['nullable', 'numeric', 'min:0', 'lte:total'],
            'balance'      => ['nullable', 'numeric', 'min:0'],
            'currency'     => ['nullable', 'string', 'size:3'],
            'status'       => ['nullable', Rule::in(OpdBillStatusEnum::getKeys())],
            'billed_by'    => ['nullable', 'integer', 'exists:users,id'],
            'remarks'      => ['nullable', 'string', 'max:1000'],
            'is_cancelled' => ['nullable', 'boolean'],
            'cancelled_at' => ['nullable', 'date', 'required_with:is_cancelled'],
            'cancelled_by' => ['nullable', 'integer', 'exists:users,id', 'required_with:is_cancelled'],
            'cancel_reason'=> ['nullable', 'string', 'max:500', 'required_with:is_cancelled'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_visit_id'] = ['required', 'integer', 'exists:opd_visits,id'];
                $common['subtotal'] = ['required', 'numeric', 'min:0'];
                $common['total'] = ['required', 'numeric', 'min:0'];
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
            'opd_visit_id.required' => 'OPD visit is required.',
            'opd_visit_id.exists'   => 'Selected OPD visit does not exist.',
            'subtotal.required'     => 'Subtotal is required.',
            'total.required'        => 'Total is required.',
            'discount.lte'          => 'Discount cannot exceed subtotal.',
            'paid.lte'              => 'Paid amount cannot exceed total.',
            'status.in'             => 'Invalid bill status.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_visit_id' => 'OPD Visit',
            'bill_no'      => 'Bill Number',
            'bill_date'    => 'Bill Date',
            'billed_by'    => 'Billed By',
            'paid'         => 'Paid Amount',
            'cancel_reason'=> 'Cancel Reason',
        ];
        return array_merge($attributes, $extra);
    }
}
