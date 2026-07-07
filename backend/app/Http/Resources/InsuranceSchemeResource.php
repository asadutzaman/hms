<?php

namespace App\Http\Resources;

class InsuranceSchemeResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $data = [
                'id'                    => $this->id,
                'uuid'                  => $this->uuid,
                'insurance_company_id'  => $this->insurance_company_id,
                'name'                  => $this->name,
                'coverage_percent'      => $this->coverage_percent,
                'max_limit'             => $this->max_limit,
                'covered_services'      => $this->covered_services,
                'is_active'             => (bool) $this->is_active,
                'status'                => $this->status,
                'created_by_name'       => $baseData['created_by_name'] ?? null,
                'updated_by_name'       => $baseData['updated_by_name'] ?? null,
                'created_at'            => $baseData['created_at'] ?? null,
                'updated_at'            => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('insuranceCompany') && $resource->insuranceCompany) {
                $data['insurance_company_name'] = $resource->insuranceCompany->name;
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
