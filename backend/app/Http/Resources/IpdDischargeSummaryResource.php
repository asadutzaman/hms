<?php

namespace App\Http\Resources;

use App\Repositories\UserRepository;

class IpdDischargeSummaryResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);

            $signedByName = '';
            if (!empty($this->signed_by)) {
                $user = (new UserRepository())->getById($this->signed_by);
                $signedByName = $user->name ?? '';
            }

            return [
                'id'                     => $this->id,
                'uuid'                   => $this->uuid,
                'admission_id'           => $this->admission_id,
                'summary_no'             => $this->summary_no,
                'admission_diagnosis'    => $this->admission_diagnosis,
                'discharge_diagnosis'    => $this->discharge_diagnosis,
                'hospital_course'        => $this->hospital_course,
                'procedures_performed'   => $this->procedures_performed,
                'discharge_condition'    => $this->discharge_condition,
                'discharge_medications'  => $this->discharge_medications,
                'follow_up_instructions' => $this->follow_up_instructions,
                'discharge_advice'       => $this->discharge_advice,
                'is_finalized'           => (bool) $this->is_finalized,
                'signed_by'              => $this->signed_by,
                'signed_by_name'         => $signedByName,
                'signed_at'              => $this->signed_at,
                'created_by_name'        => $baseData['created_by_name'] ?? null,
                'updated_by_name'        => $baseData['updated_by_name'] ?? null,
                'created_at'             => $baseData['created_at'] ?? null,
                'updated_at'             => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
