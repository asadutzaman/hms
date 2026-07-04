<?php

namespace App\Http\Resources;

class OpdPrescriptionItemResource extends BaseResource
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

            $doseDisplay = null;
            if ($resource->dose_value !== null && $resource->dose_unit) {
                $doseDisplay = rtrim(rtrim(number_format((float) $resource->dose_value, 2), '0'), '.') . ' ' . $resource->dose_unit;
            }

            $durationDisplay = null;
            if ($resource->duration_value !== null && $resource->duration_unit) {
                $durationDisplay = $resource->duration_value . ' ' . $resource->duration_unit;
            }

            $isPrn = (bool) $resource->is_prn;

            $data = [
                'id'                  => $this->id,
                'uuid'                => $this->uuid,
                'opd_prescription_id' => $this->opd_prescription_id,
                'opd_visit_id'        => $this->opd_visit_id,
                'drug_name'           => $this->drug_name,
                'generic_name'        => $this->generic_name,
                'strength'            => $this->strength,
                'dosage_form'         => $this->dosage_form,
                'dose_value'          => $this->dose_value,
                'dose_unit'           => $this->dose_unit,
                'frequency'           => $this->frequency,
                'duration_value'      => $this->duration_value,
                'duration_unit'       => $this->duration_unit,
                'route'               => $this->route,
                'instruction'         => $this->instruction,
                'is_prn'              => $isPrn,
                'unit_price'          => $this->unit_price,
                'amount'              => $this->amount,
                'sequence'            => $this->sequence,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            return array_merge($data, [
                'dose_display'     => $doseDisplay,
                'duration_display' => $durationDisplay,
            ]);
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
