<?php

namespace App\Http\Controllers;

use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Exceptions\ValidatorException;
use App\Http\Resources\OpdVisitResource;
use App\Models\OpdVisitAuditLog;
use App\Repositories\OpdVisitRepository;
use App\Repositories\CodeSequenceRepository;
use App\Services\Opd\OpdVisitService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\OpdVisitValidator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            $actorId = Auth::id();
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
            $actorId = Auth::id();
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
            $actorId = Auth::id();
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
            $actorId = Auth::id();
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
