<?php

namespace App\Http\Resources;

class BedResource extends BaseResource
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
                'id'              => $this->id,
                'ward_id'         => $this->ward_id,
                'bed_number'      => $this->bed_number,
                'bed_type'        => $this->bed_type,
                'daily_rate'      => $this->daily_rate,
                'bed_status'      => $this->bed_status,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'],
                'updated_by_name' => $baseData['updated_by_name'],
            ];

            if ($this->relationLoaded('ward') && $this->ward) {
                $data['ward_name'] = $this->ward->name;
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
