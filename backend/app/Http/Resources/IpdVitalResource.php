<?php

namespace App\Http\Resources;

class IpdVitalResource extends BaseResource
{
    /**
     * Rough adult reference ranges — used to flag abnormal readings for
     * the trend chart's highlighting (not a clinical decision-support tool).
     */
    protected static array $normalRanges = [
        'bp_systolic'      => [90, 140],
        'bp_diastolic'     => [60, 90],
        'pulse_bpm'        => [60, 100],
        'temperature_c'    => [36.1, 37.8],
        'spo2_pct'         => [95, 100],
        'respiratory_rate' => [12, 20],
    ];

    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $abnormal = [];
            foreach (self::$normalRanges as $field => [$min, $max]) {
                $value = $resource->$field ?? null;
                if ($value !== null && ((float) $value < $min || (float) $value > $max)) {
                    $abnormal[] = $field;
                }
            }

            return [
                'id'                  => $this->id,
                'uuid'                => $this->uuid,
                'admission_id'        => $this->admission_id,
                'recorded_at'         => $this->recorded_at,
                'bp_systolic'         => $this->bp_systolic,
                'bp_diastolic'        => $this->bp_diastolic,
                'bp_display'          => $this->bp_systolic && $this->bp_diastolic ? "{$this->bp_systolic}/{$this->bp_diastolic}" : null,
                'pulse_bpm'           => $this->pulse_bpm,
                'temperature_c'       => $this->temperature_c,
                'temperature_method'  => $this->temperature_method,
                'spo2_pct'            => $this->spo2_pct,
                'respiratory_rate'    => $this->respiratory_rate,
                'weight_kg'           => $this->weight_kg,
                'height_cm'           => $this->height_cm,
                'bmi'                 => $this->bmi,
                'blood_glucose_mg_dl' => $this->blood_glucose_mg_dl,
                'pain_score'          => $this->pain_score,
                'notes'               => $this->notes,
                'abnormal_fields'     => $abnormal,
                'created_by_name'     => $baseData['created_by_name'] ?? null,
                'updated_by_name'     => $baseData['updated_by_name'] ?? null,
                'created_at'          => $baseData['created_at'] ?? null,
                'updated_at'          => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
