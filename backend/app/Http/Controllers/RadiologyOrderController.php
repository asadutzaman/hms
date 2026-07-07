<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\RadiologyOrderResource;
use App\Repositories\RadiologyOrderRepository;
use App\Services\Radiology\RadiologyOrderService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\RadiologyOrderValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RadiologyOrderController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(RadiologyOrderRepository $repository, RadiologyOrderValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = RadiologyOrderResource::class;
    }

    /** Override show — eager-load patient/items/reports. */
    public function show($id)
    {
        try {
            $result = $this->repository->withRelations((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /radiology-order/worklist — active (non-terminal) orders for the radiology worklist board. */
    public function worklist(Request $request)
    {
        try {
            $rows = $this->repository->activeWorklist();
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function byPatient(Request $request, $patientId)
    {
        try {
            $rows = $this->repository->forPatient((int) $patientId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function byOpdVisit(Request $request, $opdVisitId)
    {
        try {
            $rows = $this->repository->forOpdVisit((int) $opdVisitId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function byIpdAdmission(Request $request, $admissionId)
    {
        try {
            $rows = $this->repository->forIpdAdmission((int) $admissionId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Override store — snapshots each requested study from the catalog and mints an order number. */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(RadiologyOrderService::class)->placeOrder($request->all(), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /radiology-order/{id}/cancel */
    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $result = app(RadiologyOrderService::class)->cancel((int) $id);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /radiology-order/{id}/report-pdf */
    public function reportPdf($id)
    {
        try {
            return app(RadiologyOrderService::class)->renderReportPdf((int) $id);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /radiology-order/{id}/mark-reported */
    public function markReported($id)
    {
        DB::beginTransaction();
        try {
            $result = app(RadiologyOrderService::class)->markReported((int) $id);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
