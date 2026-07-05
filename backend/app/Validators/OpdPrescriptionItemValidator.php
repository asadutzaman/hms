<?php

namespace App\Validators;

class OpdPrescriptionItemValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'opd_prescription_id' => ['nullable', 'integer', 'exists:opd_prescriptions,id'],
            'opd_visit_id'        => ['nullable', 'integer', 'exists:opd_visits,id'],
            'drug_id'             => ['nullable', 'integer', 'exists:drugs,id'],
            'drug_name'           => ['required', 'string', 'max:200'],
            'generic_name'        => ['nullable', 'string', 'max:200'],
            'strength'            => ['nullable', 'string', 'max:50'],
            'dosage_form'         => ['nullable', 'string', 'max:50'],
            'dose_value'          => ['nullable', 'numeric', 'min:0', 'max:9999.999'],
            'dose_unit'           => ['nullable', 'string', 'max:20'],
            'frequency'           => ['nullable', 'string', 'in:OD,BD,TID,QID,HS,SOS,STAT,PRN'],
            'duration_value'      => ['nullable', 'integer', 'min:1', 'max:365'],
            'duration_unit'       => ['nullable', 'string', 'in:days,weeks,months'],
            'route'               => ['nullable', 'string', 'in:oral,iv,im,sc,topical,inhalation,rectal,other'],
            'instruction'         => ['nullable', 'string', 'max:500'],
            'is_prn'              => ['nullable', 'boolean'],
            'sequence'            => ['nullable', 'integer', 'min:1', 'max:999'],
            'unit_price'          => ['nullable', 'numeric', 'min:0'],
            'amount'              => ['nullable', 'numeric', 'min:0'],
        ];

        switch ($this->request->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                $common['opd_prescription_id'] = ['required', 'integer', 'exists:opd_prescriptions,id'];
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
            'opd_prescription_id.required' => 'Prescription is required.',
            'opd_prescription_id.exists'   => 'Selected prescription does not exist.',
            'drug_name.required'           => 'Drug name is required.',
            'frequency.in'                 => 'Frequency must be one of: OD, BD, TID, QID, HS, SOS, STAT, PRN.',
            'route.in'                     => 'Route must be one of: oral, iv, im, sc, topical, inhalation, rectal, other.',
        ];
        return array_merge($messages, $extra);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $extra = [
            'opd_prescription_id' => 'Prescription',
            'drug_name'           => 'Drug Name',
            'dose_value'          => 'Dose',
            'dose_unit'           => 'Dose Unit',
            'duration_value'      => 'Duration',
            'duration_unit'       => 'Duration Unit',
        ];
        return array_merge($attributes, $extra);
    }
}
