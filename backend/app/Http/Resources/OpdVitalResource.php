<?php

namespace App\Http\Resources;

class OpdVitalResource extends BaseResource
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
            $resource = $this->resource;

            $bmiValue = $resource->bmi !== null ? (float) $resource->bmi : null;
            $bmiCategory = null;
            if ($bmiValue !== null) {
                if ($bmiValue < 18.5) {
                    $bmiCategory = 'Underweight';
                } elseif ($bmiValue < 25) {
                    $bmiCategory = 'Normal';
                } elseif ($bmiValue < 30) {
                    $bmiCategory = 'Overweight';
                } else {
                    $bmiCategory = 'Obese';
                }
            }

            $bpDisplay = null;
            if ($resource->bp_systolic !== null && $resource->bp_diastolic !== null) {
                $bpDisplay = sprintf('%d/%d', $resource->bp_systolic, $resource->bp_diastolic);
            }

            $data = [
                'id'                  => $this->id,
                'uuid'                => $this->uuid,
                'opd_visit_id'        => $this->opd_visit_id,
                'patient_id'          => $this->patient_id,
                'systolic'            => $this->bp_systolic,
                'diastolic'           => $this->bp_diastolic,
                'pulse'               => $this->pulse_bpm,
                'temperature'         => $this->temperature_c,
                'temperature_method'  => $this->temperature_method,
                'spo2'                => $this->spo2_pct,
                'respiratory_rate'    => $this->respiratory_rate,
                'weight'              => $this->weight_kg,
                'height'              => $this->height_cm,
                'bmi'                 => $this->bmi,
                'blood_glucose_mg_dl' => $this->blood_glucose_mg_dl,
                'pain_score'          => $this->pain_score,
                'notes'               => $this->notes,
                'recorded_at'         => $this->recorded_at,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            return array_merge($data, [
                'bmi_category' => $bmiCategory,
                'bp_display'   => $bpDisplay,
            ]);
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
