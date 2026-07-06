<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdDeathCertificateResource;
use App\Repositories\IpdDeathCertificateRepository;
use App\Services\Ipd\IpdDeathCertificateService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdDeathCertificateValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IpdDeathCertificateController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdDeathCertificateRepository $repository, IpdDeathCertificateValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdDeathCertificateResource::class;
    }

    /** GET /ipd-death-certificate/by-admission/{admissionId} */
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

    /** Override store — only allowed for a deceased admission; mints a certificate_no. */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $result = app(IpdDeathCertificateService::class)->create((int) $request->input('admission_id'), $request->all());

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

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $result = app(IpdDeathCertificateService::class)->update((int) $id, $request->all());

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

    /** POST /ipd-death-certificate/{id}/certify */
    public function certify(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdDeathCertificateService::class)->certify((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
