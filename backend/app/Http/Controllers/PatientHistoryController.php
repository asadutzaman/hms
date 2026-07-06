<?php

namespace App\Http\Controllers;

use App\Models\ErVisit;
use App\Models\IpdAdmission;
use App\Models\OpdVisit;
use App\Traits\Controller\RestControllerTrait;
use Carbon\Carbon;
use Exception;

class PatientHistoryController extends Controller
{
    use RestControllerTrait;

    /**
     * GET /patient-history/{patientId} — a single unified, chronologically
     * sorted timeline across OPD visits, IPD admissions, and ER visits for
     * one patient (F-16-01). Each entry carries a `type` discriminator plus
     * whatever detail that visit type needs (diagnoses/Rx/labs for OPD;
     * vitals/medications/bill for IPD; triage for ER) so the frontend can
     * render one feed without knowing which system it came from.
     */
    public function timeline(int $patientId)
    {
        try {
            $opdVisits = OpdVisit::query()
                ->where('patient_id', $patientId)
                ->with(['doctor', 'department', 'diagnoses', 'prescription.items', 'vitals', 'investigationOrders'])
                ->orderByDesc('visit_date')
                ->get()
                ->map(function ($visit) {
                    return [
                        'type'            => 'opd_visit',
                        'id'              => $visit->id,
                        'date'            => $visit->visit_date,
                        'reference_no'    => $visit->opd_no,
                        'status'          => $visit->status ?? null,
                        'doctor_name'     => optional($visit->doctor)->name_en,
                        'department_name' => optional($visit->department)->name,
                        'chief_complaint' => $visit->chief_complaint,
                        'diagnoses'       => $visit->diagnoses,
                        'prescription'    => $visit->prescription,
                        'vitals'          => $visit->vitals,
                        'investigation_orders' => $visit->investigationOrders,
                    ];
                });

            $ipdAdmissions = IpdAdmission::query()
                ->where('patient_id', $patientId)
                ->with(['ward', 'bed', 'attendingDoctor', 'vitals', 'medicationOrders', 'bill'])
                ->orderByDesc('admission_date')
                ->get()
                ->map(function ($admission) {
                    return [
                        'type'            => 'ipd_admission',
                        'id'              => $admission->id,
                        'date'            => $admission->admission_date,
                        'reference_no'    => $admission->admission_no,
                        'status'          => $admission->admission_status,
                        'doctor_name'     => optional($admission->attendingDoctor)->name_en,
                        'ward_name'       => optional($admission->ward)->name,
                        'bed_number'      => optional($admission->bed)->bed_number,
                        'discharge_date'  => $admission->discharge_date,
                        'diagnosis'       => $admission->diagnosis_at_admission,
                        'vitals'          => $admission->vitals,
                        'medication_orders' => $admission->medicationOrders,
                        'bill'            => $admission->bill,
                    ];
                });

            $erVisits = ErVisit::query()
                ->where('patient_id', $patientId)
                ->with(['triages'])
                ->orderByDesc('arrival_at')
                ->get()
                ->map(function ($visit) {
                    return [
                        'type'            => 'er_visit',
                        'id'              => $visit->id,
                        'date'            => $visit->arrival_at,
                        'reference_no'    => $visit->er_visit_no,
                        'status'          => $visit->er_status,
                        'chief_complaint' => $visit->chief_complaint,
                        'disposition'     => $visit->disposition,
                        'triages'         => $visit->triages,
                    ];
                });

            $timeline = collect()
                ->merge($opdVisits)
                ->merge($ipdAdmissions)
                ->merge($erVisits)
                ->sortByDesc(fn ($entry) => Carbon::parse($entry['date'])->timestamp)
                ->values();

            return $this->successResponse(['timeline' => $timeline]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
