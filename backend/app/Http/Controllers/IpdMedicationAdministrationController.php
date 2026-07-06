<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdMedicationAdministrationResource;
use App\Http\Resources\IpdMedicationOrderResource;
use App\Repositories\IpdMedicationAdministrationRepository;
use App\Services\Ipd\IpdMedicationService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdMedicationAdministrationValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IpdMedicationAdministrationController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdMedicationAdministrationRepository $repository, IpdMedicationAdministrationValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdMedicationAdministrationResource::class;
    }

    /** GET /ipd-medication-administration/by-admission/{admissionId} — the MAR worklist. */
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

    /** POST /ipd-medication-administration/{id}/record — Body: { administration_status, reason?, notes?, witnessed_by? } */
    public function record(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $order = app(IpdMedicationService::class)->recordAdministration((int) $id, $request->all(), $actorId);

            DB::commit();
            $response = new IpdMedicationOrderResource($order, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
