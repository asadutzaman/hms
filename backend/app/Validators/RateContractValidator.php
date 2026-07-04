<?php

namespace App\Validators;

class RateContractValidator extends BaseValidator
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
                    'supplier_id'     => ['required', 'exists:suppliers,id'],
                    'item_id'         => ['required', 'exists:items,id'],
                    'contract_price'  => ['required', 'numeric', 'min:0'],
                    'valid_from'      => ['required', 'date'],
                    'valid_to'        => ['required', 'date', 'after:valid_from'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'supplier_id'     => ['sometimes', 'required', 'exists:suppliers,id'],
                    'item_id'         => ['sometimes', 'required', 'exists:items,id'],
                    'contract_price'  => ['sometimes', 'required', 'numeric', 'min:0'],
                    'valid_from'      => ['sometimes', 'required', 'date'],
                    'valid_to'        => ['sometimes', 'required', 'date', 'after:valid_from'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'supplier_id.required'    => 'Supplier is required.',
            'item_id.required'        => 'Item is required.',
            'contract_price.required' => 'Contract price is required.',
            'valid_to.after'          => 'Valid to date must be after valid from date.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'supplier_id'    => 'Supplier',
            'item_id'        => 'Item',
            'contract_price' => 'Contract Price',
        ];

        return array_merge($attributes, $includesAttributes);
    }
}
