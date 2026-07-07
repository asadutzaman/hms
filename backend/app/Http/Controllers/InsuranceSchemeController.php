<?php

namespace App\Http\Controllers;

use App\Http\Resources\InsuranceSchemeResource;
use App\Repositories\InsuranceSchemeRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\InsuranceSchemeValidator;
use Exception;
use Illuminate\Http\Request;

class InsuranceSchemeController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'is_active'];

    use RestControllerTrait;

    public function __construct(InsuranceSchemeRepository $repository, InsuranceSchemeValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = InsuranceSchemeResource::class;
    }

    /** GET /insurance-scheme/by-company/{insuranceCompanyId} */
    public function byCompany(Request $request, $insuranceCompanyId)
    {
        try {
            $rows = $this->repository->byCompany((int) $insuranceCompanyId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
