<?php

namespace App\Validators;

class OpdPrescriptionValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_visit_id'   => ['nullable', 'integer', 'exists:opd_visits,id'],
            'patient_id'     => ['nullable', 'integer', 'exists:patients,id'],
            'prescribed_by'  => ['nullable', 'integer', 'exists:users,id'],
            'prescribed_at'  => ['nullable', 'date'],
            'advice'         => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'is_printed'     => ['nullable', 'boolean'],
            'printed_at'     => ['nullable', 'date', 'required_with:is_printed'],
            'items'          => ['nullable', 'array', 'min:1'],
            'items.*.drug_name'      => ['required_with:items', 'string', 'max:200'],
            'items.*.dose_value'     => ['nullable', 'numeric', 'min:0'],
            'items.*.dose_unit'      => ['nullable', 'string', 'max:20'],
            'items.*.frequency'      => ['nullable', 'string', 'in:OD,BD,TID,QID,HS,SOS,STAT,PRN'],
            'items.*.duration_value' => ['nullable', 'integer', 'min:1', 'max:365'],
            'items.*.duration_unit'  => ['nullable', 'string', 'in:days,weeks,months'],
            'items.*.route'          => ['nullable', 'string', 'in:oral,iv,im,sc,topical,inhalation,rectal,other'],
            'items.*.instruction'    => ['nullable', 'string', 'max:500'],
            'items.*.is_prn'         => ['nullable', 'boolean'],
            'items.*.amount'         => ['nullable', 'numeric', 'min:0'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_visit_id'] = ['required', 'integer', 'exists:opd_visits,id'];
                $common['items'] = ['required', 'array', 'min:1'];
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
        $extra = [
            'opd_visit_id.required'           => 'OPD visit is required.',
            'opd_visit_id.exists'             => 'Selected OPD visit does not exist.',
            'items.required'                  => 'At least one prescription item is required.',
            'items.min'                       => 'At least one prescription item is required.',
            'items.*.drug_name.required_with' => 'Drug name is required for each item.',
            'follow_up_date.after_or_equal'   => 'Follow-up date must be today or later.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_visit_id'   => 'OPD Visit',
            'patient_id'     => 'Patient',
            'prescribed_by'  => 'Prescriber',
            'prescribed_at'  => 'Prescribed At',
            'follow_up_date' => 'Follow-up Date',
        ];
        return array_merge($attributes, $extra);
    }
}
