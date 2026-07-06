<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\ErTriageResource;
use App\Repositories\ErTriageRepository;
use App\Services\Emergency\ErTriageService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\ErTriageValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ErTriageController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(ErTriageRepository $repository, ErTriageValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = ErTriageResource::class;
    }

    /** GET /er-triage/by-visit/{erVisitId} */
    public function byVisit(Request $request, $erVisitId)
    {
        try {
            $rows = $this->repository->forVisit((int) $erVisitId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Override store — computes color band + target time from the triage level. */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(ErTriageService::class)->triage($request->all(), $actorId);

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
}
