<?php

namespace App\Http\Resources;

class LabTestReferenceRangeResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            return [
                'id'                     => $this->id,
                'lab_test_parameter_id'  => $this->lab_test_parameter_id,
                'gender'                 => $this->gender,
                'age_min_years'          => $this->age_min_years,
                'age_max_years'          => $this->age_max_years,
                'range_low'              => $this->range_low,
                'range_high'             => $this->range_high,
                'range_text'             => $this->range_text,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
