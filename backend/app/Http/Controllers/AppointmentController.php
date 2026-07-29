<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentActionEnum;
use App\Enums\AppointmentStatusEnum;
use App\Enums\AppointmentTypeEnum;
use App\Exceptions\ValidatorException;
use App\Http\Resources\AppointmentResource;
use App\Models\AppointmentAuditLog;
use App\Models\AppointmentSlot;
use App\Repositories\AppointmentAuditLogRepository;
use App\Repositories\AppointmentRepository;
use App\Repositories\AppointmentSlotRepository;
use App\Repositories\AppointmentWaitlistRepository;
use App\Repositories\CodeSequenceRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\AppointmentValidator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'payment_status', 'notes'];

    use RestControllerTrait;

    public function __construct(AppointmentRepository $repository, AppointmentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->resource   = AppointmentResource::class;
    }

    /**
     * Override store — auto-generate appointment_no, persist slot capacity in transaction.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();

            // If a slot was selected, decrement its capacity atomically
            if (!empty($data['appointment_slot_id'])) {
                $slot = (new AppointmentSlotRepository())->decrementCapacity($data['appointment_slot_id']);
                if (!$slot) {
                    $this->errorResponse('Selected slot has no available capacity.');
                }
                if (empty($data['appointment_date'])) {
                    $data['appointment_date'] = $slot->slot_date;
                }
                if (empty($data['start_time'])) {
                    $data['start_time'] = $slot->start_time;
                }
                if (empty($data['department_id']) && !empty($slot->department_id)) {
                    $data['department_id'] = $slot->department_id;
                }
            }

            // Auto-generate appointment_no from code sequence
            $appointmentNo = (new CodeSequenceRepository())->getLatestCodeByLabel('APPOINTMENT');
            if ($appointmentNo === null) {
                $this->errorResponse('Appointment number sequence not configured. Please seed the APPOINTMENT code sequence.');
            }

            $data['appointment_no'] = $appointmentNo;
            $data['status']         = $data['status'] ?? AppointmentStatusEnum::PENDING;
            $data['type']           = $data['type'] ?? AppointmentTypeEnum::ONLINE;

            // Token = next sequential number for that doctor on that date
            if (empty($data['token_number']) && !empty($data['doctor_id']) && !empty($data['appointment_date'])) {
                $data['token_number'] = $this->repository->getNextTokenNumber($data['doctor_id'], $data['appointment_date']);
            }

            $result = $this->repository->create($data);
            if (empty($result)) {
                $this->errorResponse('Failed to create appointment.');
            }

            (new CodeSequenceRepository())->updateNextSequenceByLabel('APPOINTMENT');

            $this->writeAuditLog($result->id, AppointmentActionEnum::BOOKED, null, $result->only([
                'appointment_no', 'patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'status', 'type'
            ]));

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment/{id}/cancel — cancel an appointment, free slot capacity.
     */
    public function cancel(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'cancellation_reason' => ['nullable', 'string', 'max:500'],
            ]);

            $appointment = $this->repository->getById($id);
            if (!$appointment) {
                $this->errorResponse('Appointment not found.');
            }

            if (in_array($appointment->status, [AppointmentStatusEnum::COMPLETED, AppointmentStatusEnum::CANCELLED], true)) {
                $this->errorResponse('Appointment cannot be cancelled in its current status.');
            }

            $oldValues = $appointment->only(['status', 'cancellation_reason']);
            $appointment->status              = AppointmentStatusEnum::CANCELLED;
            $appointment->cancellation_reason = $request->input('cancellation_reason');
            $appointment->cancelled_at        = Carbon::now();
            $appointment->save();

            // Free slot capacity
            if (!empty($appointment->appointment_slot_id)) {
                (new AppointmentSlotRepository())->incrementCapacity($appointment->appointment_slot_id);
            }

            $this->writeAuditLog($appointment->id, AppointmentActionEnum::CANCELLED, $oldValues, $appointment->only(['status', 'cancellation_reason']));

            DB::commit();
            $response = new $this->resource($appointment->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment/{id}/reschedule — change date/time/slot.
     */
    public function reschedule(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'appointment_date'    => ['required', 'date'],
                'appointment_time'    => ['required', 'date_format:H:i'],
                'appointment_slot_id' => ['nullable', 'integer', 'exists:appointment_slots,id'],
                'reason'              => ['nullable', 'string', 'max:500'],
            ]);

            $appointment = $this->repository->getById($id);
            if (!$appointment) {
                $this->errorResponse('Appointment not found.');
            }
            if (in_array($appointment->status, [AppointmentStatusEnum::COMPLETED, AppointmentStatusEnum::CANCELLED], true)) {
                $this->errorResponse('Appointment cannot be rescheduled in its current status.');
            }

            $oldSlotId   = $appointment->appointment_slot_id;
            $newSlotId   = $request->input('appointment_slot_id');

            // If slot changed, free old + reserve new
            if (!empty($newSlotId) && $newSlotId !== $oldSlotId) {
                $newSlot = (new AppointmentSlotRepository())->decrementCapacity($newSlotId);
                if (!$newSlot) {
                    $this->errorResponse('Selected new slot has no available capacity.');
                }
                if ($oldSlotId) {
                    (new AppointmentSlotRepository())->incrementCapacity($oldSlotId);
                }
                $appointment->appointment_slot_id = $newSlotId;
                $appointment->appointment_time    = $newSlot->start_time;
            } else {
                $appointment->appointment_time    = $request->input('appointment_time');
            }

            $oldValues = $appointment->only(['appointment_date', 'appointment_time', 'appointment_slot_id', 'status']);
            $appointment->appointment_date    = $request->input('appointment_date');
            $appointment->rescheduled_from_id = $oldValues['rescheduled_from_id'] ?? $appointment->id;
            $appointment->status              = AppointmentStatusEnum::CONFIRMED;
            $appointment->save();

            $this->writeAuditLog($appointment->id, AppointmentActionEnum::RESCHEDULED, $oldValues, $appointment->only(['appointment_date', 'appointment_time', 'appointment_slot_id', 'status']));

            DB::commit();
            $response = new $this->resource($appointment->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment/{id}/check-in — patient arrives at counter.
     */
    public function checkIn(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $appointment = $this->repository->getById($id);
            if (!$appointment) {
                $this->errorResponse('Appointment not found.');
            }
            if ($appointment->status !== AppointmentStatusEnum::CONFIRMED && $appointment->status !== AppointmentStatusEnum::PENDING) {
                $this->errorResponse('Only confirmed or pending appointments can be checked in.');
            }

            $oldValues = $appointment->only(['status', 'checked_in_at']);
            $appointment->status        = AppointmentStatusEnum::CHECKED_IN;
            $appointment->checked_in_at = Carbon::now();
            $appointment->save();

            $this->writeAuditLog($appointment->id, AppointmentActionEnum::CHECKED_IN, $oldValues, $appointment->only(['status', 'checked_in_at']));

            DB::commit();
            $response = new $this->resource($appointment->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment/{id}/start — doctor begins consultation.
     */
    public function startConsultation(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $appointment = $this->repository->getById($id);
            if (!$appointment) {
                $this->errorResponse('Appointment not found.');
            }
            if ($appointment->status !== AppointmentStatusEnum::CHECKED_IN) {
                $this->errorResponse('Appointment must be checked in before starting consultation.');
            }

            $oldValues = $appointment->only(['status']);
            $appointment->status = AppointmentStatusEnum::IN_CONSULTATION;
            $appointment->save();

            $this->writeAuditLog($appointment->id, AppointmentActionEnum::STARTED, $oldValues, ['status' => AppointmentStatusEnum::IN_CONSULTATION]);

            DB::commit();
            $response = new $this->resource($appointment->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment/{id}/complete — consultation finished.
     */
    public function complete(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $appointment = $this->repository->getById($id);
            if (!$appointment) {
                $this->errorResponse('Appointment not found.');
            }
            if (!in_array($appointment->status, [AppointmentStatusEnum::CHECKED_IN, AppointmentStatusEnum::IN_CONSULTATION], true)) {
                $this->errorResponse('Appointment cannot be completed from its current status.');
            }

            $oldValues = $appointment->only(['status', 'completed_at']);
            $appointment->status       = AppointmentStatusEnum::COMPLETED;
            $appointment->completed_at = Carbon::now();
            $appointment->save();

            $this->writeAuditLog($appointment->id, AppointmentActionEnum::COMPLETED, $oldValues, $appointment->only(['status', 'completed_at']));

            DB::commit();
            $response = new $this->resource($appointment->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment/{id}/no-show
     */
    public function noShow(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $appointment = $this->repository->getById($id);
            if (!$appointment) {
                $this->errorResponse('Appointment not found.');
            }
            if (in_array($appointment->status, [AppointmentStatusEnum::COMPLETED, AppointmentStatusEnum::CANCELLED, AppointmentStatusEnum::NO_SHOW], true)) {
                $this->errorResponse('Appointment already in a terminal status.');
            }

            $oldValues = $appointment->only(['status']);
            $appointment->status = AppointmentStatusEnum::NO_SHOW;
            $appointment->save();

            // Free slot capacity
            if (!empty($appointment->appointment_slot_id)) {
                (new AppointmentSlotRepository())->incrementCapacity($appointment->appointment_slot_id);
            }

            $this->writeAuditLog($appointment->id, AppointmentActionEnum::NO_SHOW, $oldValues, ['status' => AppointmentStatusEnum::NO_SHOW]);

            DB::commit();
            $response = new $this->resource($appointment->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment/walk-in — quick walk-in registration at counter.
     */
    public function walkIn(Request $request)
    {
        return $this->store($this->forceWalkInType($request));
    }

    /**
     * GET /appointment/queue/doctor/{doctorId}?date=YYYY-MM-DD
     * Returns today's queue sorted by token.
     */
    public function queue(Request $request, $doctorId)
    {
        try {
            $date = $request->query('date', Carbon::now()->toDateString());
            $queue = $this->repository->getQueueForDoctor($doctorId, $date);

            $result = $queue->map(function ($appt) {
                return (new $this->resource($appt, true))->toArray($request);
            });

            return $this->successResponse([
                'doctor_id' => $doctorId,
                'date'      => $date,
                'queue'     => $result,
                'count'     => $result->count(),
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /appointment/available-slots?doctor_id=&date=
     */
    public function availableSlots(Request $request)
    {
        try {
            $request->validate([
                'doctor_id' => ['required', 'integer'],
                'date'      => ['required', 'date'],
            ]);

            $slots = $this->repository->getAvailableSlots(
                $request->input('doctor_id'),
                $request->input('date')
            );

            $result = $slots->map(function ($slot) {
                return [
                    'id'              => $slot->id,
                    'doctor_id'       => $slot->doctor_id,
                    'slot_date'       => $slot->slot_date,
                    'start_time'      => $slot->start_time,
                    'end_time'        => $slot->end_time,
                    'max_capacity'    => $slot->max_capacity,
                    'booked_count'    => $slot->booked_count,
                    'available'       => max(($slot->max_capacity - $slot->booked_count), 0),
                ];
            });

            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /appointment/{id}/audit-log
     */
    public function auditLog(Request $request, $id)
    {
        try {
            $logs = (new AppointmentAuditLogRepository())->getByAppointmentId($id);
            $result = $logs->map(function ($log) {
                return [
                    'id'                 => $log->id,
                    'action'             => $log->action,
                    'action_label'       => AppointmentActionEnum::label($log->action),
                    'old_values'         => $log->payload['old_values'] ?? null,
                    'new_values'         => $log->payload['new_values'] ?? null,
                    'performed_by'       => $log->actor_id,
                    'performed_at'       => $log->occurred_at,
                    'remarks'            => $log->remarks,
                ];
            });
            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /* ---------- Private helpers ---------- */

    private function writeAuditLog(int $appointmentId, string $action, $oldValues, $newValues, $remarks = null)
    {
        try {
            AppointmentAuditLog::create([
                'appointment_id' => $appointmentId,
                'action'         => $action,
                'payload'        => ['old_values' => $oldValues, 'new_values' => $newValues],
                'actor_id'       => Auth::id(),
                'occurred_at'    => Carbon::now(),
                'remarks'        => $remarks,
            ]);
        } catch (\Exception $e) {
            // audit log failures must not block the primary operation
        }
    }

    private function forceWalkInType(Request $request): Request
    {
        $merged = array_merge($request->all(), ['type' => AppointmentTypeEnum::WALK_IN]);
        $request->replace($merged);
        return $request;
    }
}