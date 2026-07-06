<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdMedicationOrderResource;
use App\Repositories\IpdMedicationOrderRepository;
use App\Services\Ipd\IpdMedicationService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdMedicationOrderValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IpdMedicationOrderController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdMedicationOrderRepository $repository, IpdMedicationOrderValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdMedicationOrderResource::class;
    }

    /** GET /ipd-medication-order/by-admission/{admissionId} */
    public function byAdmission(Request $request, $admissionId)
    {
        try {
            $rows = $this->repository->forAdmission((int) $admissionId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Override store — places the order and generates its administration schedule. */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdMedicationService::class)->order($request->all(), $actorId);

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

    /** POST /ipd-medication-order/{id}/discontinue — Body: { reason? } */
    public function discontinue(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdMedicationService::class)->discontinue((int) $id, $actorId, $request->input('reason'));

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-medication-order/{id}/administer-prn — record an ad-hoc PRN/SOS dose. */
    public function administerPrn(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdMedicationService::class)->recordPrnAdministration((int) $id, $request->all(), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
