<?php

namespace App\Http\Resources;

class IpdNursingAssessmentResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);

            return [
                'id'                         => $this->id,
                'uuid'                       => $this->uuid,
                'admission_id'               => $this->admission_id,
                'general_appearance'         => $this->general_appearance,
                'mobility_status'            => $this->mobility_status,
                'fall_risk_score'            => $this->fall_risk_score,
                'fall_risk_level'            => $this->fall_risk_level,
                'pressure_injury_risk_score' => $this->pressure_injury_risk_score,
                'pressure_injury_risk_level' => $this->pressure_injury_risk_level,
                'pain_assessment'            => $this->pain_assessment,
                'nutrition_risk'             => $this->nutrition_risk,
                'skin_integrity_notes'       => $this->skin_integrity_notes,
                'psychosocial_notes'         => $this->psychosocial_notes,
                'care_plan_notes'            => $this->care_plan_notes,
                'assessed_by'                => $this->assessed_by,
                'assessed_at'                => $this->assessed_at,
                'created_by_name'            => $baseData['created_by_name'] ?? null,
                'updated_by_name'            => $baseData['updated_by_name'] ?? null,
                'created_at'                 => $baseData['created_at'] ?? null,
                'updated_at'                 => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
