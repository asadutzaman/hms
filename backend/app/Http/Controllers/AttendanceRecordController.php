<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendanceRecordResource;
use App\Repositories\AttendanceRecordRepository;
use App\Services\Hr\AttendanceService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

class AttendanceRecordController extends Controller
{
    use TraitRestResponse;

    public function __construct(private AttendanceRecordRepository $repository)
    {
    }

    /** GET /attendance-record/employee/{employeeId}?start_date=&end_date= */
    public function forEmployee(Request $request, $employeeId)
    {
        try {
            $start = $request->query('start_date', now()->startOfMonth()->toDateString());
            $end = $request->query('end_date', now()->toDateString());
            $rows = $this->repository->forEmployeeRange((int) $employeeId, $start, $end);
            $response = AttendanceRecordResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /attendance-record/check-in — Body: { employee_id, shift_id? } */
    public function checkIn(Request $request)
    {
        try {
            $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id']]);
            $actorId = (new SessionService())->init()->getUserId();
            $record = app(AttendanceService::class)->checkIn(
                (int) $request->input('employee_id'),
                $request->input('shift_id') ? (int) $request->input('shift_id') : null,
                'manual',
                $actorId
            );
            return $this->successResponse((new AttendanceRecordResource($record))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /attendance-record/check-out — Body: { employee_id } */
    public function checkOut(Request $request)
    {
        try {
            $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id']]);
            $actorId = (new SessionService())->init()->getUserId();
            $record = app(AttendanceService::class)->checkOut((int) $request->input('employee_id'), 'manual', $actorId);
            return $this->successResponse((new AttendanceRecordResource($record))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /attendance-record/manual-entry — admin correction/backfill. */
    public function manualEntry(Request $request)
    {
        try {
            $request->validate([
                'employee_id'     => ['required', 'integer', 'exists:employees,id'],
                'attendance_date' => ['required', 'date'],
                'shift_id'        => ['nullable', 'integer', 'exists:shifts,id'],
                'check_in_time'   => ['nullable', 'date'],
                'check_out_time'  => ['nullable', 'date'],
                'remarks'         => ['nullable', 'string'],
            ]);
            $actorId = (new SessionService())->init()->getUserId();
            $record = app(AttendanceService::class)->manualEntry($request->all(), $actorId);
            return $this->successResponse((new AttendanceRecordResource($record))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /attendance-record/sync — F-13-02 "biometric punches synced".
     * Body: { punches: [{ employee_id, punch_time, direction: 'in'|'out' }] }
     */
    public function sync(Request $request)
    {
        try {
            $request->validate([
                'punches'                  => ['required', 'array', 'min:1'],
                'punches.*.employee_id'    => ['required', 'integer'],
                'punches.*.punch_time'     => ['required', 'date'],
                'punches.*.direction'      => ['nullable', 'in:in,out'],
            ]);
            $actorId = (new SessionService())->init()->getUserId();
            $results = app(AttendanceService::class)->syncPunches($request->input('punches'), $actorId);
            return $this->successResponse($results);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
