<?php

namespace App\Http\Controllers;

use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Exceptions\ValidatorException;
use App\Http\Resources\OpdVisitResource;
use App\Models\OpdVisitAuditLog;
use App\Repositories\AppointmentRepository;
use App\Repositories\OpdVisitRepository;
use App\Repositories\CodeSequenceRepository;
use App\Services\Opd\OpdVisitService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\OpdVisitValidator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpdVisitController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'notes'];

    use RestControllerTrait;

    public function __construct(OpdVisitRepository $repository, OpdVisitValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->resource   = OpdVisitResource::class;
    }

    /**
     * Auth::id() returns null under this app's session handling, which used
     * to write audit rows with no actor — always resolve via SessionService.
     */
    private function actorId(): ?int
    {
        return (new SessionService())->init()->getUserId();
    }

    /**
     * Override show — eager-load the full clinical graph (vitals, diagnoses,
     * prescription, investigation orders, bill, audit logs) so the frontend
     * view can render everything from a single call.
     */
    public function show($id)
    {
        try {
            $result = $this->repository->withRelations((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Override store — auto-generate opd_no and token.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();

            // Auto-generate opd_no from code sequence
            $opdNo = (new CodeSequenceRepository())->getLatestCodeByLabel('OPD_VISIT');
            if ($opdNo === null) {
                $this->errorResponse('OPD visit number sequence not configured. Please seed the OPD_VISIT code sequence.');
            }
            $data['opd_no'] = $opdNo;

            // Token = next sequential number for that doctor on that date
            if (empty($data['token_number']) && !empty($data['doctor_id']) && !empty($data['visit_date'])) {
                $data['token_number'] = $this->repository->getNextTokenNumber($data['doctor_id'], $data['visit_date']);
            }

            $actorId = $this->actorId();
            $result = app(OpdVisitService::class)->create($data, $actorId);

            (new CodeSequenceRepository())->updateNextSequenceByLabel('OPD_VISIT');

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
     * Update an OPD visit.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();
            $actorId = $this->actorId();
            $result = app(OpdVisitService::class)->update($id, $data, $actorId);

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
     * POST /opd-visit/{id}/transition — change visit status.
     */
    public function transition(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'to_status' => ['required', 'string', 'in:' . implode(',', OpdVisitStatusEnum::getKeys())],
                'remarks' => ['nullable', 'string', 'max:500'],
            ]);

            $toStatus = $request->input('to_status');
            $remarks = $request->input('remarks');
            $actorId = $this->actorId();
            $meta = $request->except(['to_status', 'remarks']);

            $result = app(OpdVisitService::class)->transition($id, $toStatus, $actorId, $remarks, $meta);

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
     * POST /opd-visit/{id}/cancel — cancel a visit.
     */
    public function cancel(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'cancellation_reason' => ['required', 'string', 'max:500'],
            ]);

            $reason = $request->input('cancellation_reason');
            $actorId = $this->actorId();
            $result = app(OpdVisitService::class)->cancel($id, $actorId, $reason);

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
     * GET /opd-visit/today — get today's visits.
     */
    public function today(Request $request)
    {
        try {
            $date = $request->query('date', Carbon::now()->toDateString());
            $visits = $this->repository->getByDate($date);

            $result = $visits->map(function ($visit) {
                return (new $this->resource($visit, true))->toArray($request);
            });

            return $this->successResponse([
                'date'  => $date,
                'visits' => $result,
                'count'  => $result->count(),
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /opd-visit/display-board — today's queue grouped by doctor, for the
     * waiting-room display board.
     */
    public function displayBoard(Request $request)
    {
        try {
            $departmentId = $request->query('department_id');
            $board = $this->repository->displayBoard($departmentId ? (int) $departmentId : null);

            return $this->successResponse($board);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-visit/{id}/call — mark a token as called on the display board.
     */
    public function call(Request $request, $id)
    {
        try {
            $actorId = $this->actorId();
            $result = $this->repository->callToken((int) $id, $actorId);

            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-visit/{id}/follow-up — schedule a follow-up appointment from
     * this visit. Defaults the doctor/department to the visit's own.
     */
    public function scheduleFollowUp(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'follow_up_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                'follow_up_time' => ['nullable', 'date_format:H:i'],
                'doctor_id'      => ['nullable', 'integer', 'exists:employees,id'],
                'notes'          => ['nullable', 'string', 'max:500'],
            ]);

            $visit = $this->repository->findById((int) $id);
            if (!$visit) {
                $this->errorResponse('OPD visit not found.');
            }

            $doctorId = $request->input('doctor_id') ?: $visit->doctor_id;
            $followUpDate = $request->input('follow_up_date');
            $startTime = $request->input('follow_up_time') ?: '10:00';
            $endTime = Carbon::createFromFormat('H:i', $startTime)->addMinutes(15)->format('H:i');

            $appointmentRepo = new AppointmentRepository();

            $appointmentNo = (new CodeSequenceRepository())->getLatestCodeByLabel('APPOINTMENT');
            if ($appointmentNo === null) {
                $this->errorResponse('Appointment number sequence not configured. Please seed the APPOINTMENT code sequence.');
            }

            $appointment = $appointmentRepo->create([
                'appointment_no'       => $appointmentNo,
                'patient_id'           => $visit->patient_id,
                'doctor_id'            => $doctorId,
                'department_id'        => $visit->department_id,
                'source'               => 'follow_up',
                'source_opd_visit_id'  => $visit->id,
                'appointment_date'     => $followUpDate,
                'start_time'           => $startTime,
                'end_time'             => $endTime,
                'status'               => 'confirmed',
                'reason_for_visit'     => 'Follow-up visit',
                'notes'                => $request->input('notes'),
                'token_number'         => $appointmentRepo->getNextTokenNumber($doctorId, $followUpDate),
                'booked_by'            => $this->actorId(),
                'created_by'           => $this->actorId(),
                'updated_by'           => $this->actorId(),
            ]);

            (new CodeSequenceRepository())->updateNextSequenceByLabel('APPOINTMENT');

            $this->repository->logAudit(
                $visit,
                OpdVisitActionEnum::FOLLOW_UP_SCHEDULED,
                null,
                null,
                $this->actorId(),
                "Follow-up appointment {$appointment->appointment_no} scheduled for {$followUpDate}",
                ['appointment_id' => $appointment->id],
            );

            DB::commit();
            return $this->successResponse([
                'appointment_id'  => $appointment->id,
                'appointment_no'  => $appointment->appointment_no,
                'appointment_date'=> $appointment->appointment_date,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /opd-visit/{id}/audit-log
     */
    public function auditLog(Request $request, $id)
    {
        try {
            $logs = $this->repository->getAuditLogs($id);
            $result = $logs->map(function ($log) {
                return [
                    'id'           => $log->id,
                    'action'       => $log->action,
                    'action_label' => OpdVisitActionEnum::label($log->action),
                    'from_status'  => $log->from_status,
                    'to_status'    => $log->to_status,
                    'actor_id'     => $log->actor_id,
                    'payload'      => $log->payload,
                    'created_at'   => $log->created_at,
                ];
            });
            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
