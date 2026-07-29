<?php

namespace App\Http\Controllers\Api\V1\Mobile\Patient;

use App\Http\Controllers\Api\V1\Mobile\BaseMobileController;
use App\Http\Controllers\PatientPortal\PatientAuthController;
use App\Http\Controllers\PatientPortal\PatientPortalAppointmentController;
use App\Http\Controllers\PatientPortal\PatientPortalBillController;
use App\Http\Controllers\PatientPortal\PatientPortalLabReportController;
use App\Http\Controllers\PatientPortal\PatientPortalPaymentController;
use App\Http\Controllers\PatientPortal\PatientPortalPrescriptionController;
use App\Http\Controllers\PatientPortal\PatientPortalTimelineController;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Patient app BFF  —  prefix /api/v1/mobile/patient  (patientAuthVerify).
 *
 * The Sprint 8/10 PatientPortal\* controllers are already a working,
 * patient-token-scoped mobile API. Rather than duplicate that (tested) query +
 * resource logic, this controller DELEGATES to them and re-wraps their bare
 * payloads in the mobile { success, data, meta } envelope so the whole
 * /api/v1 surface is consistent. Only home() and doctors() are net-new
 * aggregations the design (P1 Home, P2 Find a doctor) needs.
 *
 * PDF endpoints return a binary PDF response and are passed through untouched.
 */
class PatientMobileController extends BaseMobileController
{
    /** Unwrap a delegated portal JsonResponse (bare payload) for re-enveloping. */
    private function payload(JsonResponse $response)
    {
        return $response->getData(true);
    }

    // ---- Auth (delegates to PatientPortal\PatientAuthController) -------------

    public function requestOtp(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientAuthController::class)->requestOtp($request)));
    }

    public function verifyOtp(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientAuthController::class)->verifyOtp($request)), 'Signed in.');
    }

    public function logout(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientAuthController::class)->logout($request)), 'Signed out.');
    }

    public function me(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientAuthController::class)->me($request)));
    }

    public function updateProfile(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientAuthController::class)->updateProfile($request)), 'Profile updated.');
    }

    // ---- P1 Home ------------------------------------------------------------

    /** GET /home — the dashboard: next appointment, today's meds, due bills, latest report. */
    public function home(Request $request)
    {
        $appointments = $this->payload(app(PatientPortalAppointmentController::class)->index($request));
        $prescriptions = $this->payload(app(PatientPortalPrescriptionController::class)->index($request));
        $opdBills = $this->payload(app(PatientPortalBillController::class)->opdBills($request));
        $labReports = $this->payload(app(PatientPortalLabReportController::class)->index($request));

        $today = now()->toDateString();
        $nextAppointment = collect($appointments)
            ->filter(fn ($a) => ($a['appointment_date'] ?? null) >= $today
                && !in_array($a['status'] ?? null, ['cancelled', 'completed', 'no_show'], true))
            ->sortBy([['appointment_date', 'asc'], ['start_time', 'asc']])
            ->first();

        // Today's medications = items on the most recent prescription.
        $latestPrescription = collect($prescriptions)->first();
        $todaysMedications = $latestPrescription['items'] ?? [];

        $dueBills = collect($opdBills)
            ->filter(fn ($b) => (float) ($b['due_amount'] ?? $b['due'] ?? 0) > 0)
            ->values();

        return $this->mobileSuccess([
            'next_appointment'  => $nextAppointment,
            'todays_medications' => $todaysMedications,
            'due_bills'         => $dueBills,
            'total_due'         => (float) $dueBills->sum(fn ($b) => (float) ($b['due_amount'] ?? $b['due'] ?? 0)),
            'latest_lab_report' => collect($labReports)->first(),
        ]);
    }

    // ---- P2 Find a doctor ---------------------------------------------------

    /** GET /doctors?department_id=&search= — doctors with specialty + consultation fee. */
    public function doctors(Request $request)
    {
        $departmentId = $request->integer('department_id') ?: null;
        $search = trim((string) $request->input('search', ''));

        $doctors = app(UserRepository::class)->getDoctors(
            $departmentId,
            ['id', 'name', 'designation_id', 'department_id', 'status']
        );

        if ($search !== '') {
            $doctors = $doctors->filter(fn ($d) => stripos($d->name ?? '', $search) !== false)->values();
        }

        // Batch-resolve department names and consultation fees (avoids N+1).
        $deptNames = Department::query()
            ->whereIn('id', $doctors->pluck('department_id')->filter()->unique())
            ->pluck('name', 'id');

        $fees = DoctorSchedule::query()
            ->whereIn('doctor_id', $doctors->pluck('id'))
            ->where('status', 1)
            ->get(['doctor_id', 'consultation_fee'])
            ->groupBy('doctor_id')
            ->map(fn ($rows) => (float) $rows->min('consultation_fee'));

        $result = $doctors->map(fn ($d) => [
            'id'               => $d->id,
            'name'             => $d->name,
            'department_id'    => $d->department_id,
            'department'       => $deptNames[$d->department_id] ?? null,
            'designation_id'   => $d->designation_id,
            'consultation_fee' => $fees[$d->id] ?? null,
        ])->values();

        return $this->mobileSuccess($result, 'OK', ['total' => $result->count()]);
    }

    /** GET /doctors/{id}/slots?date= — available slots for booking (P3). */
    public function doctorSlots(Request $request, $id)
    {
        $request->merge(['doctor_id' => (int) $id]);
        return $this->mobileSuccess($this->payload(app(PatientPortalAppointmentController::class)->availableSlots($request)));
    }

    // ---- Appointments (delegates) ------------------------------------------

    public function appointments(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalAppointmentController::class)->index($request)));
    }

    public function bookAppointment(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalAppointmentController::class)->store($request)), 'Appointment booked.');
    }

    public function cancelAppointment(Request $request, $id)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalAppointmentController::class)->cancel($request, $id)), 'Appointment cancelled.');
    }

    // ---- Records: prescriptions, lab reports, bills, timeline --------------

    public function prescriptions(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalPrescriptionController::class)->index($request)));
    }

    /** PDF passthrough — returns the binary response as-is. */
    public function prescriptionPdf($id)
    {
        return app(PatientPortalPrescriptionController::class)->pdf($id);
    }

    public function labReports(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalLabReportController::class)->index($request)));
    }

    public function labReportPdf($id)
    {
        return app(PatientPortalLabReportController::class)->pdf($id);
    }

    /** GET /bills — combined OPD + IPD bills (P6). */
    public function bills(Request $request)
    {
        return $this->mobileSuccess([
            'opd' => $this->payload(app(PatientPortalBillController::class)->opdBills($request)),
            'ipd' => $this->payload(app(PatientPortalBillController::class)->ipdBills($request)),
        ]);
    }

    public function opdBillPdf($id)
    {
        return app(PatientPortalBillController::class)->opdBillPdf($id);
    }

    public function ipdBillPdf($id)
    {
        return app(PatientPortalBillController::class)->ipdBillPdf($id);
    }

    public function timeline(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalTimelineController::class)->myTimeline($request)));
    }

    // ---- Payments (delegates) ----------------------------------------------

    public function payments(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalPaymentController::class)->index($request)));
    }

    public function initiatePayment(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalPaymentController::class)->initiate($request)), 'Payment initiated.');
    }

    public function confirmPayment(Request $request, $transactionRef)
    {
        return $this->mobileSuccess($this->payload(app(PatientPortalPaymentController::class)->confirm($request, $transactionRef)), 'Payment confirmed.');
    }
}
