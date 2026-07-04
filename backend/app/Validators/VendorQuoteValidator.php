<?php

namespace App\Validators;

class VendorQuoteValidator extends BaseValidator
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
                    'supplier_id'          => ['required', 'exists:suppliers,id'],
                    'item_id'              => ['required', 'exists:items,id'],
                    'quoted_unit_price'    => ['required', 'numeric', 'min:0'],
                    'quoted_delivery_days' => ['nullable', 'integer', 'min:0'],
                    'notes'                => ['nullable', 'string'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'supplier_id'          => ['sometimes', 'required', 'exists:suppliers,id'],
                    'item_id'              => ['sometimes', 'required', 'exists:items,id'],
                    'quoted_unit_price'    => ['sometimes', 'required', 'numeric', 'min:0'],
                    'quoted_delivery_days' => ['nullable', 'integer', 'min:0'],
                    'notes'                => ['nullable', 'string'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'supplier_id.required'       => 'Supplier is required.',
            'item_id.required'           => 'Item is required.',
            'quoted_unit_price.required' => 'Quoted unit price is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'supplier_id'       => 'Supplier',
            'item_id'           => 'Item',
            'quoted_unit_price' => 'Quoted Unit Price',
        ];

        return array_merge($attributes, $includesAttributes);
    }
}
