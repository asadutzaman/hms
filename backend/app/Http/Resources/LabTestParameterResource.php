<?php

namespace App\Http\Resources;

class LabTestParameterResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $resource = $this->resource;

            $data = [
                'id'               => $this->id,
                'lab_test_id'      => $this->lab_test_id,
                'parameter_name'   => $this->parameter_name,
                'unit'             => $this->unit,
                'result_data_type' => $this->result_data_type,
                'select_options'   => $this->select_options,
                'critical_low'     => $this->critical_low,
                'critical_high'    => $this->critical_high,
                'sequence'         => $this->sequence,
            ];

            if ($resource->relationLoaded('referenceRanges')) {
                $data['reference_ranges'] = LabTestReferenceRangeResource::collection($resource->referenceRanges)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
