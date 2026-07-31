<?php

namespace App\Http\Controllers\Api\V1\Mobile\Nurse;

use App\Enums\IpdMedicationAdministrationStatusEnum;
use App\Http\Controllers\Api\V1\Mobile\BaseMobileController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\IpdAdmissionController;
use App\Http\Controllers\IpdFluidBalanceController;
use App\Http\Controllers\IpdMedicationAdministrationController;
use App\Http\Controllers\IpdNursingAssessmentController;
use App\Http\Controllers\IpdVitalController;
use App\Models\ClinicalJob;
use App\Models\CodeBlueEvent;
use App\Models\DischargeChecklist;
use App\Models\IpdAdmission;
use App\Models\IpdMedicationAdministration;
use App\Models\ShiftHandover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Nurse app BFF  —  prefix /api/v1/mobile/nurse
 * (authVerify + mobile.role:Nurse; Super Admin override). Reuses the IPD
 * medication-administration / vitals / fluid / nursing-assessment controllers,
 * and adds net-new handover (N4), task timeline (N5), rapid response (N9),
 * discharge checklist (N11) and a MAR barcode patient-verify (N6).
 */
class NurseMobileController extends BaseMobileController
{
    private function payload(JsonResponse $response)
    {
        return $response->getData(true);
    }

    // ---- N1 Shift board -----------------------------------------------------

    /** Obs are treated as due once a patient has had no vitals for this long. */
    private const VITALS_DUE_AFTER_HOURS = 4;

    public function shiftBoard()
    {
        $admitted = IpdAdmission::query()->where('status', 1)->whereNull('discharge_date')->count();
        return $this->mobileSuccess([
            'inpatients'          => $admitted,
            'meds_due_next_hour'  => $this->medsDueWithinAnHour(),
            'vitals_due'          => $this->vitalsDue(),
            'bed_board'           => $this->payload(app(BedController::class)->board()),
        ]);
    }

    /** Scheduled doses falling in the next hour on still-admitted patients. */
    private function medsDueWithinAnHour(): int
    {
        return IpdMedicationAdministration::query()
            ->where('status', 1)
            ->where('administration_status', IpdMedicationAdministrationStatusEnum::SCHEDULED)
            ->whereBetween('scheduled_at', [now(), now()->addHour()])
            ->whereHas('order.admission', fn ($q) => $q->where('admission_status', 'admitted'))
            ->count();
    }

    /** Admitted patients with no observation in the last VITALS_DUE_AFTER_HOURS. */
    private function vitalsDue(): int
    {
        $cutoff = now()->subHours(self::VITALS_DUE_AFTER_HOURS);
        return IpdAdmission::query()
            ->where('status', 1)
            ->where('admission_status', 'admitted')
            ->whereDoesntHave('vitals', fn ($q) => $q->where('recorded_at', '>=', $cutoff))
            ->count();
    }

    // ---- N2 MAR + N6 barcode verify ----------------------------------------

    public function mar(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdMedicationAdministrationController::class)->byAdmission($request, $admissionId)));
    }

    public function recordMar(Request $request, $id)
    {
        return $this->mobileSuccess($this->payload(app(IpdMedicationAdministrationController::class)->record($request, $id)), 'Administration recorded.');
    }

    /** POST /mar/verify-barcode — the "right patient" check before administering. */
    public function verifyBarcode(Request $request)
    {
        $request->validate([
            'admission_id' => ['required', 'integer'],
            'scanned_mrn'  => ['required', 'string'],
        ]);
        $adm = IpdAdmission::query()->with('patient:id,mrn,first_name,last_name')->find($request->integer('admission_id'));
        if (!$adm) {
            return $this->mobileError('Admission not found.', 404);
        }
        $match = $adm->patient && strcasecmp(trim((string) $adm->patient->mrn), trim($request->input('scanned_mrn'))) === 0;
        return $this->mobileSuccess([
            'match'   => $match,
            'patient' => $adm->patient,
        ], $match ? 'Patient verified.' : 'Barcode does not match this patient.');
    }

    // ---- N3/N10 Vitals ------------------------------------------------------

    public function vitals(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdVitalController::class)->byAdmission($request, $admissionId)));
    }

    public function recordVitals(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(IpdVitalController::class)->store($request)), 'Vitals recorded.', [], 201);
    }

    // ---- N7 IV / fluid monitoring ------------------------------------------

    public function fluidBalance(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdFluidBalanceController::class)->summary($request, $admissionId)));
    }

    public function recordFluid(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(IpdFluidBalanceController::class)->store($request)), 'Fluid recorded.', [], 201);
    }

    // ---- N8 Nursing note ----------------------------------------------------

    public function nursingNotes(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdNursingAssessmentController::class)->byAdmission($request, $admissionId)));
    }

    public function storeNursingNote(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(IpdNursingAssessmentController::class)->store($request)), 'Nursing note saved.', [], 201);
    }

    // ---- N12 Patient transfer (reuse) --------------------------------------

    public function transfer(Request $request, $id)
    {
        return $this->mobileSuccess($this->payload(app(IpdAdmissionController::class)->transferBed($request, $id)), 'Transferred.');
    }

    // ---- N9 Rapid response (net-new, shares CodeBlueEvent) -----------------

    public function rapidResponseActive()
    {
        $rows = CodeBlueEvent::query()->where('status', 1)->whereIn('state', ['active', 'responded'])
            ->with(['patient:id,first_name,last_name,mrn', 'ward:id,name'])->orderByDesc('raised_at')->get();
        return $this->mobileSuccess($rows);
    }

    public function raiseRapidResponse(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['nullable', 'integer'],
            'ward_id'    => ['nullable', 'integer'],
            'bed_id'     => ['nullable', 'integer'],
            'location'   => ['nullable', 'string', 'max:150'],
            'reason'     => ['nullable', 'string'],
        ]);
        $data['event_type'] = 'rapid_response';
        $data['state']      = 'active';
        $data['raised_by']  = $this->currentUserId();
        $data['raised_at']  = now();
        $data['created_by'] = $this->currentUserId();
        $event = CodeBlueEvent::create($data);
        return $this->mobileSuccess($event->fresh(), 'Rapid response raised.', [], 201);
    }

    // ---- N5 Task timeline (net-new clinical_jobs, nurse-scoped) ------------

    public function tasks(Request $request)
    {
        $rows = ClinicalJob::query()->where('status', 1)->where('role_type', 'nurse')
            ->when($request->filled('state'), fn ($q) => $q->where('state', $request->input('state')))
            ->orderByRaw("CASE priority WHEN 'critical' THEN 0 WHEN 'urgent' THEN 1 ELSE 2 END")
            ->orderBy('due_at')->get();
        return $this->mobileSuccess($rows);
    }

    public function completeTask(Request $request, $id)
    {
        $job = ClinicalJob::query()->where('role_type', 'nurse')->find($id);
        if (!$job) {
            return $this->mobileError('Task not found.', 404);
        }
        $job->update(['state' => 'done', 'completed_at' => now(), 'updated_by' => $this->currentUserId()]);
        return $this->mobileSuccess($job->fresh(), 'Task completed.');
    }

    // ---- N4 Shift handover (net-new, shared) -------------------------------

    public function handovers(Request $request)
    {
        $rows = ShiftHandover::query()->where('status', 1)->where('role_type', 'nurse')
            ->when($request->filled('ward_id'), fn ($q) => $q->where('ward_id', $request->integer('ward_id')))
            ->orderByDesc('id')->limit(50)->get();
        return $this->mobileSuccess($rows);
    }

    public function storeHandover(Request $request)
    {
        $data = $request->validate([
            'ward_id'     => ['nullable', 'integer'],
            'to_user_id'  => ['nullable', 'integer'],
            'shift_label' => ['nullable', 'string', 'max:60'],
            'summary'     => ['nullable', 'string'],
            'items'       => ['nullable', 'array'],
        ]);
        $data['role_type']      = 'nurse';
        $data['from_user_id']   = $this->currentUserId();
        $data['state']          = 'submitted';
        $data['handed_over_at'] = now();
        $data['created_by']     = $this->currentUserId();
        $handover = ShiftHandover::create($data);
        return $this->mobileSuccess($handover->fresh(), 'Handover submitted.', [], 201);
    }

    // ---- N11 Discharge checklist (net-new) ---------------------------------

    public function dischargeChecklist(Request $request, $admissionId)
    {
        $checklist = DischargeChecklist::query()->where('ipd_admission_id', $admissionId)->where('status', 1)->latest('id')->first();
        return $this->mobileSuccess($checklist);
    }

    public function upsertDischargeChecklist(Request $request, $admissionId)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'state' => ['nullable', 'in:in_progress,complete'],
        ]);
        $checklist = DischargeChecklist::query()->where('ipd_admission_id', $admissionId)->where('status', 1)->latest('id')->first();
        $complete = ($data['state'] ?? null) === 'complete';
        $payload = [
            'ipd_admission_id' => (int) $admissionId,
            'items'            => $data['items'],
            'state'            => $data['state'] ?? 'in_progress',
            'completed_at'     => $complete ? now() : null,
            'updated_by'       => $this->currentUserId(),
        ];
        if ($checklist) {
            $checklist->update($payload);
        } else {
            $payload['created_by'] = $this->currentUserId();
            $checklist = DischargeChecklist::create($payload);
        }
        return $this->mobileSuccess($checklist->fresh(), 'Checklist saved.');
    }
}
