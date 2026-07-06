<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdDischargeSummaryResource;
use App\Repositories\IpdDischargeSummaryRepository;
use App\Services\Ipd\IpdDischargeSummaryService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdDischargeSummaryValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IpdDischargeSummaryController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdDischargeSummaryRepository $repository, IpdDischargeSummaryValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdDischargeSummaryResource::class;
    }

    /** GET /ipd-discharge-summary/by-admission/{admissionId} */
    public function byAdmission(Request $request, $admissionId)
    {
        try {
            $result = $this->repository->forAdmission((int) $admissionId);
            if (!$result) {
                return $this->successResponse(null);
            }
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-discharge-summary/generate/{admissionId} — idempotent draft creation. */
    public function generate(Request $request, $admissionId)
    {
        DB::beginTransaction();
        try {
            $result = app(IpdDischargeSummaryService::class)->generateDraft((int) $admissionId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $result = app(IpdDischargeSummaryService::class)->update((int) $id, $request->all());

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

    /** POST /ipd-discharge-summary/{id}/sign */
    public function sign(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdDischargeSummaryService::class)->sign((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
