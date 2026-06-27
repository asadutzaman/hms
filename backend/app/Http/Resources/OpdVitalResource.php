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
            if ($resource->systolic !== null && $resource->diastolic !== null) {
                $bpDisplay = sprintf('%d/%d', $resource->systolic, $resource->diastolic);
            }

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'visit_id'       => $this->visit_id,
                'systolic'       => $this->systolic,
                'diastolic'      => $this->diastolic,
                'pulse'          => $this->pulse,
                'temperature'    => $this->temperature,
                'spo2'           => $this->spo2,
                'weight'         => $this->weight,
                'height'         => $this->height,
                'bmi'            => $this->bmi,
                'notes'          => $this->notes,
                'taken_at'       => $this->taken_at,
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
