<?php

namespace App\Http\Controllers;

use App\Enums\IpdAdmissionActionEnum;
use App\Enums\IpdAdmissionStatusEnum;
use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdAdmissionResource;
use App\Repositories\IpdAdmissionRepository;
use App\Services\Ipd\IpdAdmissionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdAdmissionValidator;
use Exception;
use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IpdAdmissionController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'diagnosis_at_admission', 'expected_discharge_date'];

    use RestControllerTrait;

    public function __construct(IpdAdmissionRepository $repository, IpdAdmissionValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdAdmissionResource::class;
    }

    /**
     * Override show — eager-load the full admission graph (bill, advances,
     * audit-derived transfer history) so the frontend view can render
     * everything from a single call.
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
     * Override store — admission is a domain action (bed lock + concurrency
     * guard + admission-no generation), not a plain field-mapped create.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = $this->repository->admit($request->all(), $actorId);

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
     * POST /ipd-admission/{id}/transfer-bed
     */
    public function transferBed(Request $request, $id)
    {
        try {
            $request->validate([
                'bed_id' => ['required', 'integer', 'exists:beds,id'],
                'reason' => ['nullable', 'string', 'max:500'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = $this->repository->transferBed(
                (int) $id,
                (int) $request->input('bed_id'),
                $actorId,
                $request->input('reason'),
            );

            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /ipd-admission/{id}/exit — discharge / DAMA / deceased, gated on
     * the bill being cleared (see IpdAdmissionService::exit()).
     * Body: { status: 'discharged'|'dama'|'deceased', remarks?, override_reason? }
     */
    public function exit(Request $request, $id)
    {
        try {
            $request->validate([
                'status'          => ['required', 'string', 'in:' . implode(',', [
                    IpdAdmissionStatusEnum::DISCHARGED,
                    IpdAdmissionStatusEnum::DAMA,
                    IpdAdmissionStatusEnum::DECEASED,
                ])],
                'remarks'         => ['nullable', 'string', 'max:1000'],
                'override_reason' => ['nullable', 'string', 'max:500'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdAdmissionService::class)->exit(
                (int) $id,
                $request->input('status'),
                $actorId,
                $request->input('remarks'),
                $request->input('override_reason'),
            );

            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /ipd-admission/{id}/audit-log
     */
    public function auditLog(Request $request, $id)
    {
        try {
            $logs = $this->repository->getAuditLogs((int) $id);
            $result = $logs->map(function ($log) {
                return [
                    'id'           => $log->id,
                    'action'       => $log->action,
                    'action_label' => IpdAdmissionActionEnum::label($log->action),
                    'from_status'  => $log->from_status,
                    'to_status'    => $log->to_status,
                    'actor_id'     => $log->actor_id,
                    'remarks'      => $log->remarks,
                    'payload'      => $log->payload,
                    'occurred_at'  => $log->occurred_at,
                ];
            });
            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
