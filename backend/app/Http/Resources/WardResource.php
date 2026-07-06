<?php

namespace App\Http\Resources;

class WardResource extends BaseResource
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
                'branch_id'       => $this->branch_id,
                'name'            => $this->name,
                'ward_type'       => $this->ward_type,
                'floor'           => $this->floor,
                'description'     => $this->description,
                'status'          => $this->status,
                'created_by_name' => $baseData['created_by_name'],
                'updated_by_name' => $baseData['updated_by_name'],
            ];

            if ($this->relationLoaded('branch') && $this->branch) {
                $data['branch_name'] = $this->branch->name;
            }

            if ($this->relationLoaded('beds')) {
                $data['bed_count'] = $this->beds->count();
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
