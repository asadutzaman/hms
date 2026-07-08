<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Traits\Controller\Functions\TraitRestResponse;
use Carbon\Carbon;
use Exception;

/**
 * F-02-08 QR Code Check-in — "self check-in at reception kiosks". A kiosk
 * has no staff login, so this is a genuinely public endpoint (restrictIp
 * only, same trust boundary as the patient-portal's unauthenticated
 * request-otp/verify-otp routes) gated only by the appointment's own
 * unguessable uuid, which is what the QR code encodes (e.g. printed on the
 * booking confirmation or emailed reminder — no new column needed since
 * every Appointment already has a uuid via the Uuid trait).
 *
 * Uses raw lowercase status literals, not AppointmentStatusEnum — see
 * project_hms_sprint8_scope memory on why that enum's uppercase constants
 * never match the DB's lowercase-only values. The existing staff
 * AppointmentController::checkIn() has this exact bug; not fixed there
 * (out of scope), worked around here in new code same as every prior
 * sprint's appointment-status fixes.
 */
class AppointmentCheckinController extends Controller
{
    use TraitRestResponse;

    /** GET /appointment-checkin/{uuid} — kiosk display/confirmation lookup. */
    public function show($uuid)
    {
        try {
            $appointment = Appointment::query()->with(['patient', 'doctor'])->where('uuid', $uuid)->first();
            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return $this->successResponse([
                'appointment_no'   => $appointment->appointment_no,
                'patient_name'     => trim(($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? '')),
                'doctor_name'      => $appointment->doctor->name_en ?? null,
                'appointment_date' => $appointment->appointment_date,
                'start_time'       => $appointment->start_time,
                'token_number'     => $appointment->token_number,
                'status'           => $appointment->status,
                'can_check_in'     => in_array($appointment->status, ['pending', 'confirmed'], true),
            ]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /appointment-checkin/{uuid} — "QR scan updates check-in status in < 3 sec". */
    public function checkIn($uuid)
    {
        try {
            $appointment = Appointment::query()->where('uuid', $uuid)->first();
            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }
            if (!in_array($appointment->status, ['pending', 'confirmed'], true)) {
                return response()->json(['message' => "This appointment cannot be checked in (status: {$appointment->status})."], 422);
            }

            $appointment->status = 'checked_in';
            $appointment->checked_in_at = Carbon::now();
            $appointment->save();

            return $this->successResponse([
                'appointment_no' => $appointment->appointment_no,
                'token_number'   => $appointment->token_number,
                'status'         => $appointment->status,
                'checked_in_at'  => $appointment->checked_in_at,
            ]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
