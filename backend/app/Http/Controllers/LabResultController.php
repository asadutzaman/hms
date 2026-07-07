<?php

namespace App\Http\Controllers;

use App\Http\Resources\LabOrderItemResource;
use App\Repositories\LabOrderItemRepository;
use App\Services\Lis\LabResultService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabResultController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(LabOrderItemRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = LabOrderItemResource::class;
    }

    /** GET /lab-result/by-order-item/{orderItemId} */
    public function byOrderItem($orderItemId)
    {
        try {
            $result = $this->repository->withResults((int) $orderItemId);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /lab-result/enter/{orderItemId} — technician entry.
     * Body: { results: [{ lab_test_parameter_id, result_value, remarks? }, ...] }
     */
    public function enter(Request $request, $orderItemId)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'results'                            => ['required', 'array', 'min:1'],
                'results.*.lab_test_parameter_id'    => ['required', 'integer', 'exists:lab_test_parameters,id'],
                'results.*.result_value'             => ['nullable', 'string', 'max:255'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(LabResultService::class)->enterResults((int) $orderItemId, $request->input('results'), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /lab-result/verify/{orderItemId} — pathologist dual sign-off. */
    public function verify($orderItemId)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(LabResultService::class)->verifyResults((int) $orderItemId, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
