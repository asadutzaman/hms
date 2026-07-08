<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\OtBookingResource;
use App\Repositories\OtBookingRepository;
use App\Services\Ot\OtBookingService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\OtBookingValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OtBookingController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    use RestControllerTrait;

    public function __construct(OtBookingRepository $repository, OtBookingValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OtBookingResource::class;
    }

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

    /** GET /ot-booking/theatre-schedule?theatre_id=&date= */
    public function theatreSchedule(Request $request)
    {
        try {
            $request->validate(['theatre_id' => ['required', 'integer'], 'date' => ['required', 'date']]);
            $rows = $this->repository->forTheatreAndDate((int) $request->input('theatre_id'), $request->input('date'));
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(OtBookingService::class)->book($request->all(), $actorId);

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

    public function reschedule(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(OtBookingService::class)->reschedule((int) $id, $request->all(), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(OtBookingService::class)->cancel((int) $id, $request->input('cancellation_reason'), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    public function start($id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(OtBookingService::class)->startSurgery((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    public function complete($id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(OtBookingService::class)->completeSurgery((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
