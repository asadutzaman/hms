<?php

namespace App\Http\Controllers\PatientPortal;

use App\Enums\AppointmentActionEnum;
use App\Exceptions\ValidatorException;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\AppointmentAuditLog;
use App\Repositories\AppointmentRepository;
use App\Repositories\AppointmentSlotRepository;
use App\Repositories\CodeSequenceRepository;
use App\Services\PatientSessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * F-02-02 Online Appointment Booking — deliberately NOT reusing
 * AppointmentController::store() as-is: that method trusts a
 * client-supplied patient_id, which a portal endpoint must never do (see
 * project_hms_sprint8_scope memory). patient_id is always derived from the
 * authenticated patient's own JWT here.
 */
class PatientPortalAppointmentController extends Controller
{
    use TraitRestResponse;

    public function __construct(private AppointmentRepository $repository)
    {
    }

    /** GET /patient-portal/appointments/available-slots?doctor_id=&date= */
    public function availableSlots(Request $request)
    {
        try {
            $request->validate([
                'doctor_id' => ['required', 'integer'],
                'date'      => ['required', 'date'],
            ]);

            // NOTE: AppointmentController::availableSlots() (the staff-facing
            // equivalent) calls $this->repository->getAvailableSlots(), but that
            // method does not exist on AppointmentRepository — a pre-existing,
            // latent bug in that endpoint (confirmed via grep; not fixed here,
            // out of this sprint's scope). The real, working method lives on
            // AppointmentSlotRepository under a different name — used correctly here.
            $slots = app(AppointmentSlotRepository::class)->getAvailableSlotsByDoctorAndDate(
                (int) $request->input('doctor_id'),
                $request->input('date')
            );

            $result = $slots->map(fn ($slot) => [
                'id'           => $slot->id,
                'doctor_id'    => $slot->doctor_id,
                'slot_date'    => $slot->slot_date,
                'start_time'   => $slot->start_time,
                'end_time'     => $slot->end_time,
                'max_capacity' => $slot->max_capacity,
                'booked_count' => $slot->booked_count,
                'available'    => max(($slot->max_capacity - $slot->booked_count), 0),
            ]);

            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/appointments — the authenticated patient's own bookings. */
    public function index(Request $request)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $rows = Appointment::query()
                ->where('patient_id', $patientId)
                ->with(['doctor', 'department'])
                ->orderByDesc('appointment_date')
                ->orderByDesc('start_time')
                ->get();

            $response = AppointmentResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /patient-portal/appointments — Body: { doctor_id, department_id?,
     * appointment_slot_id?, appointment_date, appointment_time, end_time?,
     * reason?, notes? }. patient_id/type/status are always server-derived.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $request->validate([
                'doctor_id'           => ['required', 'integer', 'exists:employees,id'],
                'department_id'       => ['nullable', 'integer', 'exists:departments,id'],
                'appointment_slot_id' => ['nullable', 'integer', 'exists:appointment_slots,id'],
                'appointment_date'    => ['required', 'date'],
                'appointment_time'    => ['required', 'date_format:H:i'],
                'end_time'            => ['nullable', 'date_format:H:i', 'after:appointment_time'],
                'reason'              => ['nullable', 'string', 'max:500'],
                'notes'               => ['nullable', 'string', 'max:1000'],
            ]);

            $data = $request->only([
                'doctor_id', 'department_id', 'appointment_slot_id',
                'appointment_date', 'appointment_time', 'end_time', 'reason', 'notes',
            ]);

            // The Appointment model's fillable field is "reason_for_visit", not
            // "reason" — the request field is named "reason" for a simpler
            // patient-facing API surface, mapped here. There is also no "type"
            // column/fillable attribute on Appointment at all (only
            // "consultation_mode"), so no type value is set below.
            if (array_key_exists('reason', $data)) {
                $data['reason_for_visit'] = $data['reason'];
                unset($data['reason']);
            }

            if (!empty($data['appointment_slot_id'])) {
                $slot = app(AppointmentSlotRepository::class)->decrementCapacity($data['appointment_slot_id']);
                if (!$slot) {
                    $this->errorResponse('Selected slot has no available capacity.');
                }
                if (empty($data['appointment_date'])) {
                    $data['appointment_date'] = $slot->slot_date;
                }
                if (empty($data['appointment_time'])) {
                    $data['appointment_time'] = $slot->start_time;
                }
                if (empty($data['end_time'])) {
                    $data['end_time'] = $slot->end_time;
                }
                if (empty($data['department_id']) && !empty($slot->department_id)) {
                    $data['department_id'] = $slot->department_id;
                }
            }

            // The Appointment model's real NOT NULL columns are start_time/end_time,
            // not "appointment_time" — the request field is named appointment_time
            // (matching AppointmentValidator's convention) but must be mapped here.
            // The staff-facing AppointmentController::store() never does this
            // mapping either (a pre-existing bug — confirmed via the appointments
            // table migration showing start_time/end_time as NOT NULL with no
            // default — not fixed there, out of this sprint's scope).
            $data['start_time'] = $data['appointment_time'];
            if (empty($data['end_time'])) {
                $data['end_time'] = Carbon::createFromFormat('H:i', $data['appointment_time'])->addMinutes(30)->format('H:i');
            }

            $appointmentNo = (new CodeSequenceRepository())->getLatestCodeByLabel('APPOINTMENT');
            if ($appointmentNo === null) {
                $this->errorResponse('Appointment number sequence not configured.');
            }

            $data['appointment_no'] = $appointmentNo;
            $data['patient_id']     = $patientId;
            // NOTE: AppointmentStatusEnum's constants are UPPERCASE ('PENDING',
            // 'CANCELLED', ...) but the appointments table's DB check constraint
            // only allows lowercase values ('pending', 'cancelled', ...) — a
            // pre-existing mismatch confirmed to affect the shared staff
            // AppointmentController identically (every status transition there
            // uses this same enum). Not fixed at the source (out of scope); this
            // controller uses the raw lowercase values the DB actually accepts.
            $data['status']         = 'pending';
            // 'online' is the DB check-constraint-approved value for the source
            // column (enum: online, walk_in, phone, follow_up, referral, staff) —
            // there is no 'patient_portal' option.
            $data['source']         = 'online';

            if (empty($data['token_number']) && !empty($data['doctor_id']) && !empty($data['appointment_date'])) {
                $data['token_number'] = $this->repository->getNextTokenNumber($data['doctor_id'], $data['appointment_date']);
            }

            $result = $this->repository->create($data);
            if (empty($result)) {
                $this->errorResponse('Failed to create appointment.');
            }

            (new CodeSequenceRepository())->updateNextSequenceByLabel('APPOINTMENT');

            AppointmentAuditLog::create([
                'appointment_id' => $result->id,
                'action'         => AppointmentActionEnum::BOOKED,
                'payload'        => ['old_values' => null, 'new_values' => $result->only([
                    'appointment_no', 'patient_id', 'doctor_id', 'appointment_date', 'start_time', 'end_time', 'status',
                ])],
                'actor_id'       => null,
                'occurred_at'    => Carbon::now(),
                'remarks'        => 'Booked via patient portal (self-service)',
            ]);

            DB::commit();
            return $this->successResponse((new AppointmentResource($result->fresh(['doctor', 'department']), false))->toArray($request));
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /patient-portal/appointments/{id}/cancel — only own appointment, up to 2h before (F-02-04). */
    public function cancel(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $appointment = Appointment::query()->where('patient_id', $patientId)->find($id);
            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            // See the NOTE in store() — AppointmentStatusEnum's constants are
            // uppercase but the DB only accepts lowercase values, so this
            // controller compares/assigns against the raw lowercase strings.
            if (in_array($appointment->status, ['completed', 'cancelled'], true)) {
                $this->errorResponse('Appointment cannot be cancelled in its current status.');
            }

            $appointmentAt = Carbon::parse($appointment->appointment_date->toDateString() . ' ' . $appointment->start_time);
            if (Carbon::now()->diffInMinutes($appointmentAt, false) < 120) {
                $this->errorResponse('Appointments can only be cancelled up to 2 hours in advance. Please call the front desk.');
            }

            $appointment->status              = 'cancelled';
            $appointment->cancellation_reason = $request->input('cancellation_reason', 'Cancelled by patient via portal');
            $appointment->cancelled_at        = Carbon::now();
            $appointment->save();

            if (!empty($appointment->appointment_slot_id)) {
                app(AppointmentSlotRepository::class)->incrementCapacity($appointment->appointment_slot_id);
            }

            AppointmentAuditLog::create([
                'appointment_id' => $appointment->id,
                'action'         => AppointmentActionEnum::CANCELLED,
                'payload'        => ['old_values' => ['status' => 'pending'], 'new_values' => ['status' => 'cancelled']],
                'actor_id'       => null,
                'occurred_at'    => Carbon::now(),
                'remarks'        => 'Cancelled via patient portal (self-service)',
            ]);

            DB::commit();
            return $this->successResponse((new AppointmentResource($appointment->fresh(), false))->toArray($request));
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
