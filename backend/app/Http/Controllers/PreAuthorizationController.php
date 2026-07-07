<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\PreAuthorizationResource;
use App\Repositories\PreAuthorizationRepository;
use App\Services\Insurance\PreAuthorizationService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\PreAuthorizationValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreAuthorizationController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    use RestControllerTrait;

    public function __construct(PreAuthorizationRepository $repository, PreAuthorizationValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PreAuthorizationResource::class;
    }

    /** Override show — eager-load patient/insurance relations. */
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

    /** GET /pre-authorization/by-patient/{patientId} */
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

    /** GET /pre-authorization/pending — submitted/under_review, oldest first. */
    public function pending(Request $request)
    {
        try {
            $rows = $this->repository->pending();
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Override store — mints a PA number. */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(PreAuthorizationService::class)->submit($request->all(), $actorId);

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

    /** POST /pre-authorization/{id}/under-review */
    public function markUnderReview($id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(PreAuthorizationService::class)->markUnderReview((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /pre-authorization/{id}/approve — Body: { approved_amount, notes? } */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate(['approved_amount' => ['required', 'numeric', 'min:0']]);
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(PreAuthorizationService::class)->approve(
                (int) $id,
                $actorId,
                (float) $request->input('approved_amount'),
                $request->input('notes')
            );

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

    /** POST /pre-authorization/{id}/reject — Body: { notes } */
    public function reject(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate(['notes' => ['required', 'string']]);
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(PreAuthorizationService::class)->reject((int) $id, $actorId, $request->input('notes'));

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
