<?php

namespace App\Validators;

use App\Enums\IpdFluidBalanceTypeEnum;
use Illuminate\Validation\Rule;

class IpdFluidBalanceValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'balance_type' => ['nullable', Rule::in(IpdFluidBalanceTypeEnum::getKeys())],
            'category'     => ['nullable', 'string', 'max:30'],
            'amount_ml'    => ['nullable', 'numeric', 'min:0'],
            'shift'        => ['nullable', 'string', 'in:morning,evening,night'],
            'recorded_at'  => ['nullable', 'date'],
            'notes'        => ['nullable', 'string'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $common['admission_id'] = ['required', 'integer', 'exists:ipd_admissions,id'];
                $common['balance_type'] = ['required', Rule::in(IpdFluidBalanceTypeEnum::getKeys())];
                $common['category'] = ['required', 'string', 'max:30'];
                $common['amount_ml'] = ['required', 'numeric', 'min:0.01'];
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

        return array_merge($messages, [
            'amount_ml.required' => 'Amount is required.',
            'category.required'  => 'Category is required.',
        ]);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'balance_type' => 'Type',
            'amount_ml'    => 'Amount (mL)',
        ]);
    }
}
