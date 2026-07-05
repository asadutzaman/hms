<?php

namespace App\Http\Resources;

class OpdProcedureResource extends BaseResource
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
                'id'             => $this->id,
                'opd_visit_id'   => $this->opd_visit_id,
                'patient_id'     => $this->patient_id,
                'procedure_name' => $this->procedure_name,
                'procedure_code' => $this->procedure_code,
                'performed_by'   => $this->performed_by,
                'performed_at'   => $this->performed_at,
                'notes'          => $this->notes,
                'outcome'        => $this->outcome,
                'status'         => $this->status,
                'created_by_name' => $baseData['created_by_name'],
                'updated_by_name' => $baseData['updated_by_name'],
            ];

            if ($this->performer) {
                $includesData['performed_by_name'] = $this->performer->name_en;
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
