<?php

namespace App\Http\Resources;

class ReferralResource extends BaseResource
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
                'id'                         => $this->id,
                'opd_visit_id'               => $this->opd_visit_id,
                'patient_id'                 => $this->patient_id,
                'referring_doctor_id'        => $this->referring_doctor_id,
                'referred_to_department_id'  => $this->referred_to_department_id,
                'referred_to_doctor_id'      => $this->referred_to_doctor_id,
                'external_facility_name'     => $this->external_facility_name,
                'reason'                     => $this->reason,
                'urgency'                    => $this->urgency,
                'referral_status'            => $this->referral_status,
                'notes'                      => $this->notes,
                'status'                     => $this->status,
                'created_by_name'            => $baseData['created_by_name'],
                'updated_by_name'            => $baseData['updated_by_name'],
                'created_at'                 => $baseData['created_at'],
            ];

            if ($this->referringDoctor) {
                $includesData['referring_doctor_name'] = $this->referringDoctor->name_en;
            }
            if ($this->referredToDepartment) {
                $includesData['referred_to_department_name'] = $this->referredToDepartment->name;
            }
            if ($this->referredToDoctor) {
                $includesData['referred_to_doctor_name'] = $this->referredToDoctor->name_en;
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
