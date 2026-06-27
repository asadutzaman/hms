<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class OpdBillItemValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_bill_id'    => ['nullable', 'integer', 'exists:opd_bills,id'],
            'item_type'      => ['nullable', Rule::in(['consultation', 'prescription', 'investigation', 'procedure', 'other'])],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id'   => ['nullable', 'integer'],
            'description'    => ['nullable', 'string', 'max:500'],
            'quantity'       => ['nullable', 'integer', 'min:1'],
            'unit_price'     => ['nullable', 'numeric', 'min:0'],
            'discount'       => ['nullable', 'numeric', 'min:0'],
            'amount'         => ['nullable', 'numeric', 'min:0'],
            'sequence'       => ['nullable', 'integer', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_bill_id'] = ['required', 'integer', 'exists:opd_bills,id'];
                $common['description'] = ['required', 'string', 'max:500'];
                $common['quantity'] = ['required', 'integer', 'min:1'];
                $common['unit_price'] = ['required', 'numeric', 'min:0'];
                $common['amount'] = ['required', 'numeric', 'min:0'];
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
            'opd_bill_id.required' => 'Bill is required.',
            'opd_bill_id.exists'   => 'Selected bill does not exist.',
            'description.required' => 'Description is required.',
            'quantity.required'    => 'Quantity is required.',
            'quantity.min'         => 'Quantity must be at least 1.',
            'unit_price.required'  => 'Unit price is required.',
            'amount.required'      => 'Amount is required.',
            'item_type.in'         => 'Item type must be one of: consultation, prescription, investigation, procedure, other.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_bill_id'    => 'Bill',
            'item_type'      => 'Item Type',
            'reference_type' => 'Reference Type',
            'reference_id'   => 'Reference',
            'unit_price'     => 'Unit Price',
        ];
        return array_merge($attributes, $extra);
    }
}
