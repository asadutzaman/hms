<?php

namespace App\Http\Resources;

class IpdAdmissionResource extends BaseResource
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
                'id'                        => $this->id,
                'admission_no'              => $this->admission_no,
                'patient_id'                => $this->patient_id,
                'opd_visit_id'              => $this->opd_visit_id,
                'admission_type'            => $this->admission_type,
                'attending_doctor_id'       => $this->attending_doctor_id,
                'department_id'             => $this->department_id,
                'ward_id'                   => $this->ward_id,
                'bed_id'                    => $this->bed_id,
                'branch_id'                 => $this->branch_id,
                'admission_date'            => $this->admission_date,
                'expected_discharge_date'   => $this->expected_discharge_date,
                'discharge_date'            => $this->discharge_date,
                'admission_status'          => $this->admission_status,
                'diagnosis_at_admission'    => $this->diagnosis_at_admission,
                'discharge_override_reason' => $this->discharge_override_reason,
                'admitted_by'               => $this->admitted_by,
                'discharged_by'             => $this->discharged_by,
                'status'                    => $this->status,
                'created_by_name'           => $baseData['created_by_name'],
                'updated_by_name'           => $baseData['updated_by_name'],
            ];

            if ($this->relationLoaded('patient') && $this->patient) {
                $data['patient_name'] = trim(($this->patient->first_name ?? '') . ' ' . ($this->patient->last_name ?? ''));
                $data['mrn'] = $this->patient->mrn;
            }
            if ($this->relationLoaded('attendingDoctor') && $this->attendingDoctor) {
                $data['attending_doctor_name'] = $this->attendingDoctor->name_en;
            }
            if ($this->relationLoaded('ward') && $this->ward) {
                $data['ward_name'] = $this->ward->name;
            }
            if ($this->relationLoaded('bed') && $this->bed) {
                $data['bed_number'] = $this->bed->bed_number;
            }
            if ($this->relationLoaded('bill') && $this->bill) {
                $data['bill'] = (new IpdBillResource($this->bill))->resolve();
            }
            if ($this->relationLoaded('advancePayments')) {
                $data['advance_payments'] = $this->advancePayments->map(fn ($a) => [
                    'id'             => $a->id,
                    'amount'         => $a->amount,
                    'payment_method' => $a->payment_method,
                    'advance_status' => $a->advance_status,
                    'received_at'    => $a->received_at,
                ]);
            }
            if ($this->relationLoaded('auditLogs')) {
                $data['audit_logs'] = $this->auditLogs->map(fn ($log) => [
                    'id'          => $log->id,
                    'action'      => $log->action,
                    'from_status' => $log->from_status,
                    'to_status'   => $log->to_status,
                    'actor_id'    => $log->actor_id,
                    'remarks'     => $log->remarks,
                    'payload'     => $log->payload,
                    'occurred_at' => $log->occurred_at,
                ]);
            }

            return array_merge($data, $includesData);
        } catch (\Exception $e) {
            return parent::toArray($request);
        }
    }

}
