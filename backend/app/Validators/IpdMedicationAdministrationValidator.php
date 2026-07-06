<?php

namespace App\Validators;

use App\Enums\IpdMedicationAdministrationStatusEnum;
use Illuminate\Validation\Rule;

class IpdMedicationAdministrationValidator extends BaseValidator
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
            case 'PUT':
            case 'PATCH':
                return [
                    'administration_status' => ['required', Rule::in(IpdMedicationAdministrationStatusEnum::getKeys())],
                    'reason'                => ['nullable', 'string', 'max:255'],
                    'notes'                 => ['nullable', 'string'],
                    'witnessed_by'          => ['nullable', 'integer', 'exists:users,id'],
                ];
            default:
                return [];
        }
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        return array_merge($attributes, [
            'administration_status' => 'Status',
        ]);
    }
}
