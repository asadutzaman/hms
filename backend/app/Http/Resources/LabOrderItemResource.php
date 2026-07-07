<?php

namespace App\Http\Resources;

use App\Enums\LabOrderItemStatusEnum;

class LabOrderItemResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $resource = $this->resource;
            $status = $this->item_status;

            $data = [
                'id'                    => $this->id,
                'uuid'                  => $this->uuid,
                'lab_order_id'          => $this->lab_order_id,
                'lab_test_id'           => $this->lab_test_id,
                'test_name_snapshot'    => $this->test_name_snapshot,
                'sample_type_snapshot'  => $this->sample_type_snapshot,
                'price_snapshot'        => $this->price_snapshot,
                'item_status'           => $status,
                'item_status_label'     => LabOrderItemStatusEnum::label($status),
                'sequence'              => $this->sequence,
            ];

            if ($resource->relationLoaded('results')) {
                $data['results'] = LabResultResource::collection($resource->results)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
