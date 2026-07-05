<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Services\Opd\PrescriptionDispenseService;
use App\Services\SessionService;
use App\Validators\PrescriptionDispenseValidator;
use App\Repositories\PrescriptionDispenseRepository;
use App\Http\Resources\PrescriptionDispenseResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;

class PrescriptionDispenseController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PrescriptionDispenseRepository $repository, PrescriptionDispenseValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PrescriptionDispenseResource::class;
    }

    /**
     * POST /prescription-dispense/{prescriptionId}
     * body: { items: [{ opd_prescription_item_id, dispensed_quantity, witnessed_by?, remarks? }] }
     */
    public function dispense(Request $request, $prescriptionId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $lines = $request->input('items', []);

            $dispense = app(PrescriptionDispenseService::class)->dispense((int) $prescriptionId, $lines, $actorId);

            $response = new $this->resource($dispense, false);
            return $this->successResourceResponse($response);
        } catch (ApiException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /prescription-dispense/{prescriptionId}/shortfall
     * body: { items: [{ opd_prescription_item_id, dispensed_quantity }] }
     */
    public function shortfall(Request $request)
    {
        try {
            $branchId = (new SessionService())->init()->getUserBranchId();
            $lines = $request->input('items', []);

            $shortfall = app(PrescriptionDispenseService::class)->computeShortfall((int) $branchId, $lines);
            return $this->successResponse(['shortfall' => $shortfall]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /prescription-dispense/for-prescription/{prescriptionId}
     */
    public function forPrescription($prescriptionId)
    {
        try {
            $dispenses = $this->repository->newQuery()
                ->with('items')
                ->where('opd_prescription_id', $prescriptionId)
                ->orderByDesc('dispensed_at')
                ->get();

            $response = $this->resource::collection($dispenses);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
