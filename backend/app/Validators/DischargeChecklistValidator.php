<?php

namespace App\Validators;

class DischargeChecklistValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'ipd_admission_id' => ['nullable', 'integer'],
            'items'            => ['nullable', 'array'],
            'state'            => ['nullable', 'string', 'max:20'],
            'completed_at'     => ['nullable', 'date'],
            'status'           => ['nullable', 'integer'],
        ];

        switch ($this->request->method()) {
            case 'POST':
                $common['ipd_admission_id'] = ['required', 'integer'];
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
        return array_merge(parent::messages(), [
            'ipd_admission_id.required' => 'Admission is required.',
        ]);
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
