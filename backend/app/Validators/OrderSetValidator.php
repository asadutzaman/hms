<?php

namespace App\Validators;

class OrderSetValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'name'          => ['nullable', 'string', 'max:150'],
            'category'      => ['nullable', 'string', 'max:60'],
            'description'   => ['nullable', 'string'],
            'items'         => ['nullable', 'array'],
            'is_global'     => ['nullable', 'boolean'],
            'owner_user_id' => ['nullable', 'integer'],
            'status'        => ['nullable', 'integer'],
        ];

        switch ($this->request->method()) {
            case 'POST':
                $common['name'] = ['required', 'string', 'max:150'];
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
            'name.required' => 'Order set name is required.',
        ]);
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
