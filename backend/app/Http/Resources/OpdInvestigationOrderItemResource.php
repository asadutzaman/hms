<?php

namespace App\Http\Resources;

class OpdInvestigationOrderItemResource extends BaseResource
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

            $tatMinutes = $resource->tat_minutes !== null ? (int) $resource->tat_minutes : null;
            $tatDisplay = null;
            if ($tatMinutes !== null && $tatMinutes > 0) {
                $hours = $tatMinutes / 60;
                $tatDisplay = sprintf('%.1f h', $hours);
            }

            $resultStatusLabel = ucfirst((string) ($resource->result_status ?? ''));

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'order_id'       => $this->order_id,
                'lab_test_id'    => $this->lab_test_id,
                'test_code'      => $this->test_code,
                'test_name'      => $this->test_name,
                'department'     => $this->department,
                'sample_type'    => $this->sample_type,
                'amount'         => $this->amount,
                'tat_minutes'    => $this->tat_minutes,
                'tat_display'    => $tatDisplay,
                'sequence'       => $this->sequence,
                'result_status'  => $this->result_status,
                'result_status_label' => $resultStatusLabel,
                'result_value'   => $this->result_value,
                'result_remarks' => $this->result_remarks,
                'resulted_at'    => $this->resulted_at,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
