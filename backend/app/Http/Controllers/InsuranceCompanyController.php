<?php

namespace App\Http\Controllers;

use App\Http\Resources\InsuranceCompanyResource;
use App\Repositories\InsuranceCompanyRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\InsuranceCompanyValidator;
use Exception;

class InsuranceCompanyController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'is_active'];

    use RestControllerTrait;

    public function __construct(InsuranceCompanyRepository $repository, InsuranceCompanyValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = InsuranceCompanyResource::class;
    }

    /** Override show — eager-load schemes. */
    public function show($id)
    {
        try {
            $result = $this->repository->withSchemes((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
