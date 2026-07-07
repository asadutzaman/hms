<?php

namespace App\Http\Resources;

use App\Enums\InsuranceClaimStatusEnum;

class InsuranceClaimResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;
            $status = $this->claim_status;

            $data = [
                'id'                    => $this->id,
                'uuid'                  => $this->uuid,
                'claim_no'              => $this->claim_no,
                'patient_id'            => $this->patient_id,
                'insurance_company_id'  => $this->insurance_company_id,
                'insurance_scheme_id'   => $this->insurance_scheme_id,
                'pre_authorization_id'  => $this->pre_authorization_id,
                'policy_number'         => $this->policy_number,
                'billable_type'         => $this->billable_type,
                'billable_id'           => $this->billable_id,
                'claimed_amount'        => $this->claimed_amount,
                'approved_amount'       => $this->approved_amount,
                'claim_status'          => $status,
                'claim_status_label'    => InsuranceClaimStatusEnum::label($status),
                'submitted_by'          => $this->submitted_by,
                'submitted_at'          => $this->submitted_at,
                'settled_at'            => $this->settled_at,
                'notes'                 => $this->notes,
                'created_by_name'       => $baseData['created_by_name'] ?? null,
                'updated_by_name'       => $baseData['updated_by_name'] ?? null,
                'created_at'            => $baseData['created_at'] ?? null,
                'updated_at'            => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('patient') && $resource->patient) {
                $data['patient_name'] = trim(($resource->patient->first_name ?? '') . ' ' . ($resource->patient->last_name ?? ''));
                $data['mrn'] = $resource->patient->mrn ?? null;
            }
            if ($resource->relationLoaded('insuranceCompany') && $resource->insuranceCompany) {
                $data['insurance_company_name'] = $resource->insuranceCompany->name;
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
