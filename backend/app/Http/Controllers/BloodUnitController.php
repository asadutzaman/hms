<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\BloodUnitResource;
use App\Repositories\BloodUnitRepository;
use App\Services\BloodBank\BloodUnitService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BloodUnitController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(BloodUnitRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = BloodUnitResource::class;
    }

    /** GET /blood-unit/inventory?blood_group=&component_type= */
    public function inventory(Request $request)
    {
        try {
            $rows = $this->repository->inventory($request->input('blood_group'), $request->input('component_type'));
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /blood-unit/inventory-summary */
    public function inventorySummary()
    {
        try {
            return $this->successResponse($this->repository->inventorySummary());
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /blood-unit/expiring-soon?days= */
    public function expiringSoon(Request $request)
    {
        try {
            $rows = $this->repository->expiringSoon((int) $request->query('days', 7));
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /blood-unit/{id}/screening — Body: { hiv, hbsag, hcv, vdrl, malaria } each 'negative'|'positive' */
    public function recordScreening(Request $request, $id)
    {
        try {
            $request->validate([
                'hiv'     => ['required', 'in:negative,positive'],
                'hbsag'   => ['required', 'in:negative,positive'],
                'hcv'     => ['required', 'in:negative,positive'],
                'vdrl'    => ['required', 'in:negative,positive'],
                'malaria' => ['required', 'in:negative,positive'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BloodUnitService::class)->recordScreening((int) $id, $request->all(), $actorId);

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
