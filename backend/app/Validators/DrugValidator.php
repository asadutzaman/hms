<?php

namespace App\Validators;

class DrugValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'logistic_id'         => ['required', 'integer', 'exists:logistics,id'],
            'item_category_id'    => ['required', 'integer', 'exists:item_categories,id'],
            'brand_id'            => ['required', 'integer', 'exists:brands,id'],
            'base_unit_id'        => ['required', 'integer', 'exists:units,id'],
            'name_en'             => ['required', 'string', 'max:255'],
            'name_bn'             => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:255'],
            'reorder_qty'         => ['required', 'numeric', 'min:0'],
            'generic_name'        => ['required', 'string', 'max:255'],
            'brand_name'          => ['nullable', 'string', 'max:255'],
            'strength'            => ['nullable', 'string', 'max:100'],
            'dosage_form'         => ['required', 'string', 'in:tablet,capsule,syrup,injection,ointment,drops,inhaler,other'],
            'hsn_code'            => ['nullable', 'string', 'max:50'],
            'is_controlled'       => ['nullable', 'boolean'],
            'controlled_schedule' => ['nullable', 'required_if:is_controlled,true', 'string', 'max:100'],
            'generic_drug_id'     => ['nullable', 'integer', 'exists:drugs,id'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
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

        $includesMessages = [
            'generic_name.required' => 'Generic name is required.',
            'dosage_form.required'  => 'Dosage form is required.',
            'name_en.required'      => 'Item name (English) is required.',
            'name_bn.required'      => 'Item name (Bangla) is required.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'generic_name' => 'Generic Name',
            'brand_name'   => 'Brand Name',
            'dosage_form'  => 'Dosage Form',
            'name_en'      => 'Item Name (English)',
            'name_bn'      => 'Item Name (Bangla)',
        ];

        return array_merge($attributes, $includesAttributes);
    }
}
