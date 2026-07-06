<?php

namespace App\Validators;

use App\Enums\IpdPaymentMethodEnum;
use Illuminate\Validation\Rule;

class IpdAdvancePaymentValidator extends BaseValidator
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
                    'admission_id'   => ['required', 'integer', 'exists:ipd_admissions,id'],
                    'amount'         => ['required', 'numeric', 'min:0.01'],
                    'payment_method' => ['required', Rule::in(IpdPaymentMethodEnum::getKeys())],
                    'reference_no'   => ['nullable', 'string', 'max:100'],
                    'notes'          => ['nullable', 'string', 'max:500'],
                    'received_at'    => ['nullable', 'date'],
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
            'admission_id.required'   => 'Admission is required.',
            'admission_id.exists'     => 'Selected admission does not exist.',
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
            'admission_id'   => 'Admission',
            'payment_method' => 'Payment Method',
            'reference_no'   => 'Reference Number',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
