<?php

namespace App\Http\Resources;

class LeaveTypeResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);

            $includesData = [];
            $data = [
                'id'                 => $this->id,
                'uuid'               => $this->uuid,
                'name'               => $this->name,
                'max_days_per_year'  => $this->max_days_per_year,
                'is_paid'            => $this->is_paid,
                'description'        => $this->description,
                'status'             => $this->status,
                'created_by_name'    => $baseData['created_by_name'],
                'updated_by_name'    => $baseData['updated_by_name'],
            ];
            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
