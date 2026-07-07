<?php

namespace App\Http\Resources;

use App\Enums\PreAuthorizationStatusEnum;

class PreAuthorizationResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;
            $status = $this->pa_status;

            $data = [
                'id'                    => $this->id,
                'uuid'                  => $this->uuid,
                'pa_no'                 => $this->pa_no,
                'patient_id'            => $this->patient_id,
                'ipd_admission_id'      => $this->ipd_admission_id,
                'opd_visit_id'          => $this->opd_visit_id,
                'insurance_company_id'  => $this->insurance_company_id,
                'insurance_scheme_id'   => $this->insurance_scheme_id,
                'policy_number'         => $this->policy_number,
                'estimated_amount'      => $this->estimated_amount,
                'approved_amount'       => $this->approved_amount,
                'diagnosis'             => $this->diagnosis,
                'treatment_plan'        => $this->treatment_plan,
                'pa_status'             => $status,
                'pa_status_label'       => PreAuthorizationStatusEnum::label($status),
                'requested_by'          => $this->requested_by,
                'requested_at'          => $this->requested_at,
                'responded_at'          => $this->responded_at,
                'responded_by'          => $this->responded_by,
                'response_notes'        => $this->response_notes,
                'tat_hours'             => $this->responded_at && $this->requested_at
                    ? round($this->requested_at->diffInMinutes($this->responded_at) / 60, 1)
                    : null,
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
            if ($resource->relationLoaded('insuranceScheme') && $resource->insuranceScheme) {
                $data['insurance_scheme_name'] = $resource->insuranceScheme->name;
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
