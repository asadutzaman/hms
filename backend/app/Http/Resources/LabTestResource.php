<?php

namespace App\Http\Resources;

class LabTestResource extends BaseResource
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
                'category'        => $this->category,
                'sample_type'     => $this->sample_type,
                'tat_hours'       => $this->tat_hours,
                'default_price'   => $this->default_price,
                'is_active'       => (bool) $this->is_active,
                'description'     => $this->description,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'      => $baseData['created_at'] ?? null,
                'updated_at'      => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('parameters')) {
                $data['parameters'] = LabTestParameterResource::collection($resource->parameters)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
