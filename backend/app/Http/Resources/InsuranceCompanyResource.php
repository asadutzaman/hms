<?php

namespace App\Http\Resources;

class InsuranceCompanyResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $data = [
                'id'              => $this->id,
                'uuid'            => $this->uuid,
                'code'            => $this->code,
                'name'            => $this->name,
                'tpa_type'        => $this->tpa_type,
                'contact_person'  => $this->contact_person,
                'phone'           => $this->phone,
                'email'           => $this->email,
                'address'         => $this->address,
                'credit_limit'    => $this->credit_limit,
                'is_active'       => (bool) $this->is_active,
                'description'     => $this->description,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'      => $baseData['created_at'] ?? null,
                'updated_at'      => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('schemes')) {
                $data['schemes'] = InsuranceSchemeResource::collection($resource->schemes)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
