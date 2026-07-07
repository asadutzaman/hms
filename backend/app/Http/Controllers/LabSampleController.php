<?php

namespace App\Http\Controllers;

use App\Http\Resources\LabSampleResource;
use App\Repositories\LabSampleRepository;
use App\Services\Lis\LabSampleService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabSampleController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(LabSampleRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = LabSampleResource::class;
    }

    public function byOrder(Request $request, $orderId)
    {
        try {
            $rows = $this->repository->forOrder((int) $orderId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /lab-sample/collect — Body: { lab_order_id, sample_type } */
    public function collect(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'lab_order_id' => ['required', 'integer', 'exists:lab_orders,id'],
                'sample_type'  => ['required', 'string', 'max:50'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(LabSampleService::class)->collect(
                (int) $request->input('lab_order_id'),
                $request->input('sample_type'),
                $actorId,
            );

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /lab-sample/receive-by-barcode — Body: { barcode } */
    public function receiveByBarcode(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(['barcode' => ['required', 'string']]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(LabSampleService::class)->receive($request->input('barcode'), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /lab-sample/{id}/reject — Body: { reason } */
    public function reject(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate(['reason' => ['required', 'string', 'max:255']]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(LabSampleService::class)->reject((int) $id, $actorId, $request->input('reason'));

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
