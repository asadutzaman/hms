<?php

namespace App\Http\Resources;

use App\Enums\OpdVisitStatusEnum;
use App\Repositories\AppointmentRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\PatientRepository;

class OpdVisitResource extends BaseResource
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
            $baseData    = parent::toArray($request);
            $resource    = $this->resource;
            $includes    = [];

            if (!empty($resource->patient_id)) {
                $patient = (new PatientRepository())->getById($resource->patient_id);
                $includes['patient_name']   = $patient ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) : '';
                $includes['patient_mrn']    = $patient->mrn ?? '';
                $includes['patient_phone']  = $patient->primary_phone ?? '';
                $includes['patient_gender'] = $patient->gender ?? '';
                $includes['patient_dob']    = $patient->date_of_birth ?? null;
            }

            if (!empty($resource->doctor_id)) {
                $doctor = (new EmployeeRepository())->getById($resource->doctor_id);
                $includes['doctor_name']        = $doctor->name_en ?? '';
                $includes['doctor_name_bn']     = $doctor->name_bn ?? '';
                $includes['doctor_employee_id'] = $doctor->employee_id ?? '';
            }

            if (!empty($resource->department_id)) {
                $dept = (new DepartmentRepository())->getById($resource->department_id);
                $includes['department_name'] = $dept->name ?? '';
                $includes['department_code'] = $dept->code ?? '';
            }

            if (!empty($resource->appointment_id)) {
                $appt = (new AppointmentRepository())->getById($resource->appointment_id);
                $includes['appointment_no']   = $appt->appointment_no ?? '';
                $includes['consultation_fee'] = $appt->consultation_fee ?? 0;
            }

            $includes['status_label']     = OpdVisitStatusEnum::label($resource->status ?? null);
            $includes['visit_type_label'] = ucfirst(str_replace('_', ' ', (string) ($resource->visit_type ?? '')));
            $includes['is_terminal']      = in_array($resource->status, ['closed', 'cancelled'], true);
            $includes['is_active']        = in_array($resource->status, ['waiting', 'vitals_taken', 'in_consultation'], true);

            $data = [
                'id'                    => $this->id,
                'uuid'                  => $this->uuid,
                'opd_no'                => $this->opd_no,
                'patient_id'            => $this->patient_id,
                'appointment_id'        => $this->appointment_id,
                'doctor_id'             => $this->doctor_id,
                'department_id'         => $this->department_id,
                'visit_type'            => $this->visit_type,
                'visit_date'            => $this->visit_date,
                'token_number'          => $this->token_number,
                'status'                => $this->status,
                'chief_complaint'       => $this->chief_complaint,
                'history'               => $this->history,
                'examination'           => $this->examination,
                'clinical_notes'        => $this->clinical_notes,
                'advice'                => $this->advice,
                'consultation_start_at' => $this->consultation_start_at,
                'consultation_end_at'   => $this->consultation_end_at,
                'closed_at'             => $this->closed_at,
                'cancellation_reason'   => $this->cancellation_reason,
                'cancelled_at'          => $this->cancelled_at,
                'closed_by'             => $this->closed_by,
                'created_by_name'       => $baseData['created_by_name'] ?? null,
                'updated_by_name'       => $baseData['updated_by_name'] ?? null,
                'created_at'            => $baseData['created_at'] ?? null,
                'updated_at'            => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('vitals')) {
                $includes['vitals'] = $resource->vitals
                    ? (new OpdVitalResource($resource->vitals))->toArray($request)
                    : null;
            }
            if ($resource->relationLoaded('diagnoses')) {
                $includes['diagnoses'] = OpdDiagnosisResource::collection($resource->diagnoses)->toArray($request);
            }
            if ($resource->relationLoaded('prescription')) {
                $includes['prescription'] = $resource->prescription
                    ? (new OpdPrescriptionResource($resource->prescription))->toArray($request)
                    : null;
            }
            if ($resource->relationLoaded('investigationOrders')) {
                $includes['investigation_orders'] = OpdInvestigationOrderResource::collection($resource->investigationOrders)->toArray($request);
            }
            if ($resource->relationLoaded('bill')) {
                $includes['bill'] = $resource->bill
                    ? (new OpdBillResource($resource->bill))->toArray($request)
                    : null;
            }
            if ($resource->relationLoaded('auditLogs')) {
                $includes['audit_logs'] = OpdVisitAuditLogResource::collection($resource->auditLogs)->toArray($request);
            }

            return array_merge($data, $includes);
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
