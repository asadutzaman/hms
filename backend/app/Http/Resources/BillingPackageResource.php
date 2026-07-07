<?php

namespace App\Http\Resources;

class BillingPackageResource extends BaseResource
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
                'package_type'    => $this->package_type,
                'fixed_price'     => $this->fixed_price,
                'description'     => $this->description,
                'is_active'       => (bool) $this->is_active,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'      => $baseData['created_at'] ?? null,
                'updated_at'      => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('items')) {
                $data['items'] = BillingPackageItemResource::collection($resource->items)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
