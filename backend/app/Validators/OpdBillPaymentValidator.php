<?php

namespace App\Validators;

use App\Enums\OpdPaymentMethodEnum;
use Illuminate\Validation\Rule;

class OpdBillPaymentValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_bill_id'       => ['nullable', 'integer', 'exists:opd_bills,id'],
            'payment_method'    => ['nullable', Rule::in(OpdPaymentMethodEnum::getKeys())],
            'amount'            => ['nullable', 'numeric', 'min:0.01'],
            'transaction_no'    => ['nullable', 'string', 'max:100'],
            'reference_no'      => ['nullable', 'string', 'max:100'],
            'paid_at'           => ['nullable', 'date'],
            'paid_by'           => ['nullable', 'integer', 'exists:users,id'],
            'card_last4'        => ['nullable', 'string', 'size:4'],
            'card_brand'        => ['nullable', 'string', 'max:50'],
            'bank_name'         => ['nullable', 'string', 'max:100'],
            'cheque_no'         => ['nullable', 'string', 'max:50'],
            'cheque_date'       => ['nullable', 'date'],
            'mobile_provider'   => ['nullable', 'string', 'max:50'],
            'mobile_account'    => ['nullable', 'string', 'max:50'],
            'insurance_company' => ['nullable', 'string', 'max:100'],
            'insurance_policy'  => ['nullable', 'string', 'max:100'],
            'remarks'           => ['nullable', 'string', 'max:500'],
            'status'            => ['nullable', Rule::in(['pending', 'confirmed', 'failed', 'reversed'])],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_bill_id'] = ['required', 'integer', 'exists:opd_bills,id'];
                $common['payment_method'] = ['required', Rule::in(OpdPaymentMethodEnum::getKeys())];
                $common['amount'] = ['required', 'numeric', 'min:0.01'];
                $common['paid_at'] = ['required', 'date'];
                $common['cheque_no'] = ['required_if:payment_method,cheque', 'nullable', 'string', 'max:50'];
                $common['cheque_date'] = ['required_if:payment_method,cheque', 'nullable', 'date'];
                $common['bank_name'] = ['required_if:payment_method,cheque', 'nullable', 'string', 'max:100'];
                $common['card_last4'] = ['required_if:payment_method,card', 'nullable', 'string', 'size:4'];
                $common['mobile_provider'] = ['required_if:payment_method,mobile_banking', 'nullable', 'string', 'max:50'];
                $common['mobile_account'] = ['required_if:payment_method,mobile_banking', 'nullable', 'string', 'max:50'];
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
            'opd_bill_id.required'      => 'Bill is required.',
            'opd_bill_id.exists'        => 'Selected bill does not exist.',
            'payment_method.required'   => 'Payment method is required.',
            'payment_method.in'         => 'Invalid payment method.',
            'amount.required'           => 'Amount is required.',
            'amount.min'                => 'Amount must be greater than 0.',
            'paid_at.required'          => 'Payment date is required.',
            'cheque_no.required_if'     => 'Cheque number is required for cheque payments.',
            'cheque_date.required_if'   => 'Cheque date is required for cheque payments.',
            'bank_name.required_if'     => 'Bank name is required for cheque payments.',
            'card_last4.required_if'    => 'Card last 4 digits are required for card payments.',
            'mobile_provider.required_if' => 'Mobile provider is required for mobile banking.',
            'mobile_account.required_if'  => 'Mobile account is required for mobile banking.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_bill_id'    => 'Bill',
            'payment_method' => 'Payment Method',
            'transaction_no' => 'Transaction Number',
            'reference_no'   => 'Reference Number',
            'paid_at'        => 'Paid At',
            'paid_by'        => 'Paid By',
            'card_last4'     => 'Card Last 4',
            'bank_name'      => 'Bank Name',
            'cheque_no'      => 'Cheque Number',
            'cheque_date'    => 'Cheque Date',
        ];
        return array_merge($attributes, $extra);
    }
}
