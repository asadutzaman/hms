<?php

namespace App\Validators;

class PurchaseOrderValidator extends BaseValidator
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
                    'supplier_id'             => ['required', 'exists:suppliers,id'],
                    'order_date'              => ['required', 'date'],
                    'expected_delivery_date'  => ['nullable', 'date'],
                    'notes'                   => ['nullable', 'string'],
                    'poItemsList'             => ['required', 'array'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'supplier_id'             => ['required', 'exists:suppliers,id'],
                    'order_date'              => ['required', 'date'],
                    'expected_delivery_date'  => ['nullable', 'date'],
                    'notes'                   => ['nullable', 'string'],
                    'poItemsList'             => ['required', 'array'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'supplier_id.required' => 'Supplier is required.',
            'order_date.required'  => 'Order date is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'supplier_id' => 'Supplier',
            'order_date'  => 'Order Date',
        ];

        return array_merge($attributes, $includesAttributes);
    }
}
