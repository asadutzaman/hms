<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdFluidBalanceResource;
use App\Repositories\IpdFluidBalanceRepository;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdFluidBalanceValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IpdFluidBalanceController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdFluidBalanceRepository $repository, IpdFluidBalanceValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdFluidBalanceResource::class;
    }

    /** GET /ipd-fluid-balance/by-admission/{admissionId} */
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

    /** GET /ipd-fluid-balance/summary/{admissionId} — daily intake/output/balance totals. */
    public function summary(Request $request, $admissionId)
    {
        try {
            $result = $this->repository->dailySummary((int) $admissionId);
            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Override store — stamps recorded_by/recorded_at from the session. */
    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();
            $data['recorded_by'] = (new SessionService())->init()->getUserId();
            $data['recorded_at'] = $data['recorded_at'] ?? now();

            $result = $this->repository->create($data);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
