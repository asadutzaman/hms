<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\LeaveRequestResource;
use App\Repositories\LeaveRequestRepository;
use App\Services\Hr\LeaveRequestService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(LeaveRequestRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = LeaveRequestResource::class;
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

    /** GET /leave-request/employee/{employeeId} */
    public function forEmployee($employeeId)
    {
        try {
            $rows = $this->repository->forEmployee((int) $employeeId);
            $response = $this->resource::collection($rows)->toArray(request());
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Override store — applies for leave (goes straight to SUBMITTED, entering the workflow engine). */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'employee_id'   => ['required', 'integer', 'exists:employees,id'],
                'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
                'start_date'    => ['required', 'date'],
                'end_date'      => ['required', 'date', 'after_or_equal:start_date'],
                'reason'        => ['nullable', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(LeaveRequestService::class)->apply($request->all(), $actorId);

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
