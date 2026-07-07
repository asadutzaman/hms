<?php

namespace App\Http\Resources;

use App\Enums\RadOrderItemStatusEnum;

class RadiologyOrderItemResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $resource = $this->resource;
            $status = $this->item_status;

            $data = [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'radiology_order_id'   => $this->radiology_order_id,
                'radiology_test_id'    => $this->radiology_test_id,
                'test_name_snapshot'   => $this->test_name_snapshot,
                'modality_snapshot'    => $this->modality_snapshot,
                'price_snapshot'       => $this->price_snapshot,
                'item_status'          => $status,
                'item_status_label'    => RadOrderItemStatusEnum::label($status),
                'sequence'             => $this->sequence,
            ];

            if ($resource->relationLoaded('report') && $resource->report) {
                $data['report'] = (new RadiologyReportResource($resource->report))->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
