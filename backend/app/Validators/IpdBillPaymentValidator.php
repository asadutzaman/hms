<?php

namespace App\Validators;

use App\Enums\IpdPaymentMethodEnum;
use Illuminate\Validation\Rule;

class IpdBillPaymentValidator extends BaseValidator
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
                    'ipd_bill_id'    => ['required', 'integer', 'exists:ipd_bills,id'],
                    'amount'         => ['required', 'numeric', 'min:0.01'],
                    'payment_method' => ['required', Rule::in(IpdPaymentMethodEnum::getKeys())],
                    'reference_no'   => ['nullable', 'string', 'max:100'],
                    'notes'          => ['nullable', 'string', 'max:500'],
                    'paid_at'        => ['nullable', 'date'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'notes' => ['nullable', 'string', 'max:500'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'ipd_bill_id.required'    => 'Bill is required.',
            'ipd_bill_id.exists'      => 'Selected bill does not exist.',
            'amount.required'        => 'Amount is required.',
            'amount.min'             => 'Amount must be greater than 0.',
            'payment_method.required' => 'Payment method is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'ipd_bill_id'    => 'Bill',
            'payment_method' => 'Payment Method',
            'reference_no'   => 'Reference Number',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
