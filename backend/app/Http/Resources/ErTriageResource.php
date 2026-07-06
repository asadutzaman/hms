<?php

namespace App\Http\Resources;

use App\Enums\ErTriageLevelEnum;

class ErTriageResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $level = $this->triage_level;

            return [
                'id'               => $this->id,
                'uuid'             => $this->uuid,
                'er_visit_id'      => $this->er_visit_id,
                'triage_level'     => $level,
                'triage_level_label' => $level ? ErTriageLevelEnum::label($level) : null,
                'color_band'       => $this->color_band,
                'target_minutes'   => $this->target_minutes,
                'bp_systolic'      => $this->bp_systolic,
                'bp_diastolic'     => $this->bp_diastolic,
                'bp_display'       => $this->bp_systolic && $this->bp_diastolic ? "{$this->bp_systolic}/{$this->bp_diastolic}" : null,
                'pulse_bpm'        => $this->pulse_bpm,
                'temperature_c'    => $this->temperature_c,
                'spo2_pct'         => $this->spo2_pct,
                'respiratory_rate' => $this->respiratory_rate,
                'notes'            => $this->notes,
                'triaged_by'       => $this->triaged_by,
                'triaged_at'       => $this->triaged_at,
                'created_by_name'  => $baseData['created_by_name'] ?? null,
                'updated_by_name'  => $baseData['updated_by_name'] ?? null,
                'created_at'       => $baseData['created_at'] ?? null,
                'updated_at'       => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
