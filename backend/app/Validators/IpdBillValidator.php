<?php

namespace App\Validators;

class IpdBillValidator extends BaseValidator
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
                    'admission_id' => ['required', 'integer', 'exists:ipd_admissions,id'],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'discount' => ['nullable', 'numeric', 'min:0'],
                    'tax'      => ['nullable', 'numeric', 'min:0'],
                ];
            default:
                break;
        }
    }

    public function messages()
    {
        $messages = parent::messages();

        $includesMessages = [
            'admission_id.required' => 'Admission is required.',
            'admission_id.exists'   => 'Selected admission does not exist.',
        ];

        return array_merge($messages, $includesMessages);
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $includesAttributes = [
            'admission_id' => 'Admission',
        ];

        return array_merge($attributes, $includesAttributes);
    }

}
