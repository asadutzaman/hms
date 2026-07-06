<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\ErVisitResource;
use App\Repositories\ErVisitRepository;
use App\Services\Emergency\ErVisitService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\ErVisitValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ErVisitController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(ErVisitRepository $repository, ErVisitValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = ErVisitResource::class;
    }

    /** Override show — eager-load patient + triage history in one call. */
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

    /** GET /er-visit/board — the active ER worklist (not yet at a terminal status). */
    public function board(Request $request)
    {
        try {
            $rows = $this->repository->activeBoard();
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Override store — quick registration: mints er_visit_no, stamps registered_by. */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(ErVisitService::class)->register($request->all(), $actorId);

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

    /** POST /er-visit/{id}/start-treatment */
    public function startTreatment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $result = app(ErVisitService::class)->startTreatment((int) $id);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /er-visit/{id}/dispose — Body: { disposition: discharged|lwbs|deceased } */
    public function dispose(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'disposition' => ['required', 'string', 'in:discharged,lwbs,deceased'],
            ]);

            $result = app(ErVisitService::class)->dispose((int) $id, $request->input('disposition'));

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

    /**
     * POST /er-visit/{id}/link-admission — Body: { admission_id }
     * Links this ER visit to an IPD admission created elsewhere (the
     * admission itself doesn't know about ER visits — see
     * ErVisitService::linkAdmission()).
     */
    public function linkAdmission(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'admission_id' => ['required', 'integer', 'exists:ipd_admissions,id'],
            ]);

            app(ErVisitService::class)->linkAdmission((int) $id, (int) $request->input('admission_id'));
            $result = $this->repository->withRelations((int) $id);

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
