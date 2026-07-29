<?php

namespace App\Http\Controllers\Api\V1\Mobile\Doctor;

use App\Http\Controllers\Api\V1\Mobile\BaseMobileController;
use App\Http\Controllers\DoctorPortalController;
use App\Http\Controllers\LabOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpdPrescriptionController;
use App\Http\Controllers\PatientController;
use App\Http\Resources\PatientResource;
use App\Models\CodeBlueEvent;
use App\Models\Patient;
use App\Models\SoapNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Doctor app BFF  —  prefix /api/v1/mobile/doctor  (authVerify + mobile.role:Doctor).
 *
 * Reuses the existing DoctorPortal / Patient / LabOrder / OpdPrescription /
 * Notification controllers for the read/write clinical operations, and adds
 * two net-new entities the design needs: SOAP notes (D3) and Code Blue (D6,
 * shared with the nurse Rapid Response screen).
 */
class DoctorMobileController extends BaseMobileController
{
    private function payload(JsonResponse $response)
    {
        return $response->getData(true);
    }

    // ---- D1 Today / dashboard ----------------------------------------------

    public function dashboard()
    {
        return $this->mobileSuccess($this->payload(app(DoctorPortalController::class)->dashboard()));
    }

    public function schedule(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(DoctorPortalController::class)->appointments($request)));
    }

    /** Critical banner + inbox — in-app notifications for this doctor. */
    public function alerts()
    {
        return $this->mobileSuccess([
            'notifications' => $this->payload(app(NotificationController::class)->my()),
            'unread_count'  => $this->payload(app(NotificationController::class)->unreadCount()),
        ]);
    }

    public function markAlertRead($id)
    {
        return $this->mobileSuccess($this->payload(app(NotificationController::class)->markRead($id)), 'Marked read.');
    }

    // ---- D2 Patient profile -------------------------------------------------

    public function patient($id)
    {
        $patient = Patient::query()->find($id);
        if (!$patient) {
            return $this->mobileError('Patient not found.', 404);
        }
        return $this->mobileSuccess((new PatientResource($patient))->toArray(request()));
    }

    public function patientHistory($id)
    {
        return $this->mobileSuccess($this->payload(app(DoctorPortalController::class)->patientHistory($id)));
    }

    public function patientAllergies(Request $request, $id)
    {
        return $this->mobileSuccess($this->payload(app(PatientController::class)->allergies($request, $id)));
    }

    // ---- D3 SOAP note (net-new) ---------------------------------------------

    /** GET /soap-notes?patient_id=&opd_visit_id=&ipd_admission_id= */
    public function soapNotes(Request $request)
    {
        $query = SoapNote::query()->where('status', 1);
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }
        if ($request->filled('opd_visit_id')) {
            $query->where('opd_visit_id', $request->integer('opd_visit_id'));
        }
        if ($request->filled('ipd_admission_id')) {
            $query->where('ipd_admission_id', $request->integer('ipd_admission_id'));
        }
        $rows = $query->with('author:id,name')->orderByDesc('noted_at')->orderByDesc('id')->get();
        return $this->mobileSuccess($rows);
    }

    public function storeSoapNote(Request $request)
    {
        $data = $request->validate([
            'patient_id'       => ['required', 'integer'],
            'opd_visit_id'     => ['nullable', 'integer'],
            'ipd_admission_id' => ['nullable', 'integer'],
            'subjective'       => ['nullable', 'string'],
            'objective'        => ['nullable', 'string'],
            'assessment'       => ['nullable', 'string'],
            'plan'             => ['nullable', 'string'],
        ]);

        $data['author_user_id'] = $this->currentUserId();
        $data['created_by']     = $this->currentUserId();
        $data['noted_at']       = now();

        $note = SoapNote::create($data);
        return $this->mobileSuccess($note->fresh('author:id,name'), 'SOAP note saved.', [], 201);
    }

    public function updateSoapNote(Request $request, $id)
    {
        $note = SoapNote::query()->find($id);
        if (!$note) {
            return $this->mobileError('SOAP note not found.', 404);
        }
        $data = $request->validate([
            'subjective' => ['nullable', 'string'],
            'objective'  => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
            'plan'       => ['nullable', 'string'],
        ]);
        $data['updated_by'] = $this->currentUserId();
        $note->update($data);
        return $this->mobileSuccess($note->fresh(), 'SOAP note updated.');
    }

    // ---- D4 Prescription (reuse) -------------------------------------------

    public function savePrescription(Request $request, $visitId)
    {
        return $this->mobileSuccess($this->payload(app(OpdPrescriptionController::class)->saveForVisit($request, $visitId)), 'Prescription saved.');
    }

    public function latestPrescription(Request $request, $patientId)
    {
        return $this->mobileSuccess($this->payload(app(DoctorPortalController::class)->latestPrescription($patientId, $request)));
    }

    public function recentDrugs()
    {
        return $this->mobileSuccess($this->payload(app(DoctorPortalController::class)->recentDrugs()));
    }

    // ---- D5 Lab orders (reuse) ---------------------------------------------

    public function labOrders(Request $request, $patientId)
    {
        return $this->mobileSuccess($this->payload(app(LabOrderController::class)->byPatient($request, $patientId)));
    }

    public function createLabOrder(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(LabOrderController::class)->store($request)), 'Lab order placed.', [], 201);
    }

    /** Results to sign — the doctor's lab worklist (reuse). */
    public function resultsInbox(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(LabOrderController::class)->worklist($request)));
    }

    // ---- D6 Code Blue / Rapid Response (net-new, shared with nurse) --------

    /** GET /code-blue — active events. */
    public function codeBlueActive()
    {
        $rows = CodeBlueEvent::query()
            ->where('status', 1)
            ->whereIn('state', ['active', 'responded'])
            ->with(['patient:id,first_name,last_name,mrn', 'ward:id,name'])
            ->orderByDesc('raised_at')
            ->get();
        return $this->mobileSuccess($rows);
    }

    public function raiseCodeBlue(Request $request)
    {
        $data = $request->validate([
            'event_type' => ['nullable', 'in:code_blue,rapid_response'],
            'patient_id' => ['nullable', 'integer'],
            'ward_id'    => ['nullable', 'integer'],
            'bed_id'     => ['nullable', 'integer'],
            'location'   => ['nullable', 'string', 'max:150'],
            'severity'   => ['nullable', 'string', 'max:20'],
            'reason'     => ['nullable', 'string'],
        ]);
        $data['event_type'] = $data['event_type'] ?? 'code_blue';
        $data['state']      = 'active';
        $data['raised_by']  = $this->currentUserId();
        $data['raised_at']  = now();
        $data['created_by'] = $this->currentUserId();

        $event = CodeBlueEvent::create($data);
        return $this->mobileSuccess($event->fresh(), 'Code Blue raised.', [], 201);
    }

    public function respondCodeBlue(Request $request, $id)
    {
        $event = CodeBlueEvent::query()->find($id);
        if (!$event) {
            return $this->mobileError('Event not found.', 404);
        }
        $responders = $event->responders ?? [];
        $responders[] = ['user_id' => $this->currentUserId(), 'at' => now()->toDateTimeString()];
        $event->update([
            'state'        => 'responded',
            'responders'   => $responders,
            'responded_at' => $event->responded_at ?? now(),
            'updated_by'   => $this->currentUserId(),
        ]);
        return $this->mobileSuccess($event->fresh(), 'Acknowledged.');
    }

    public function resolveCodeBlue(Request $request, $id)
    {
        $event = CodeBlueEvent::query()->find($id);
        if (!$event) {
            return $this->mobileError('Event not found.', 404);
        }
        $event->update([
            'state'         => 'resolved',
            'outcome_notes' => $request->input('outcome_notes'),
            'resolved_at'   => now(),
            'updated_by'    => $this->currentUserId(),
        ]);
        return $this->mobileSuccess($event->fresh(), 'Resolved.');
    }
}
