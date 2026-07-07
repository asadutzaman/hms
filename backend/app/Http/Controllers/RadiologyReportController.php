<?php

namespace App\Http\Controllers;

use App\Http\Resources\RadiologyOrderItemResource;
use App\Repositories\RadiologyOrderItemRepository;
use App\Services\Radiology\RadiologyReportService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;

class RadiologyReportController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(RadiologyOrderItemRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = RadiologyOrderItemResource::class;
    }

    /** GET /radiology-report/by-order-item/{orderItemId} */
    public function byOrderItem($orderItemId)
    {
        try {
            $result = $this->repository->withReport((int) $orderItemId);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /radiology-report/save-draft/{orderItemId} — Body: { radiology_report_template_id?, findings?, impression? } */
    public function saveDraft(Request $request, $orderItemId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(RadiologyReportService::class)->saveDraft((int) $orderItemId, $request->all(), $actorId);

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /radiology-report/finalize/{orderItemId} */
    public function finalize($orderItemId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(RadiologyReportService::class)->finalize((int) $orderItemId, $actorId);

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /radiology-report/verify/{orderItemId} */
    public function verify($orderItemId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(RadiologyReportService::class)->verify((int) $orderItemId, $actorId);

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
