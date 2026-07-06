<?php

namespace App\Validators;

use App\Enums\IpdBillItemTypeEnum;
use Illuminate\Validation\Rule;

class IpdBillItemValidator extends BaseValidator
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
                    'ipd_bill_id' => ['required', 'integer', 'exists:ipd_bills,id'],
                    'item_type'   => ['required', Rule::in(IpdBillItemTypeEnum::getKeys())],
                    'description' => ['required', 'string', 'max:255'],
                    'quantity'    => ['required', 'numeric', 'min:0.01'],
                    'unit_price'  => ['required', 'numeric', 'min:0'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'item_type'   => ['nullable', Rule::in(IpdBillItemTypeEnum::getKeys())],
                    'description' => ['nullable', 'string', 'max:255'],
                    'quantity'    => ['nullable', 'numeric', 'min:0.01'],
                    'unit_price'  => ['nullable', 'numeric', 'min:0'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'ipd_bill_id.required' => 'Bill is required.',
            'ipd_bill_id.exists'   => 'Selected bill does not exist.',
            'description.required' => 'Description is required.',
            'quantity.required'    => 'Quantity is required.',
            'unit_price.required'  => 'Unit price is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'ipd_bill_id' => 'Bill',
            'item_type'   => 'Item Type',
            'unit_price'  => 'Unit Price',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
