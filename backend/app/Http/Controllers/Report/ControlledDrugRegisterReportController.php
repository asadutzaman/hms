<?php

namespace App\Http\Controllers\Report;

use Exception;
use App\Http\Controllers\Controller;
use App\Repositories\Report\ControlledDrugRegisterReportRepository;
use App\Http\Resources\Report\ControlledDrugRegisterReportResource;
use App\Traits\Controller\RestControllerTrait;

class ControlledDrugRegisterReportController extends Controller
{
    use RestControllerTrait;

    private $repository;
    private $resource;

    public function __construct(ControlledDrugRegisterReportRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = ControlledDrugRegisterReportResource::class;
    }

    public function getRegisterList()
    {
        try {
            $result = ($this->repository->init())->getRegisterList();
            $result['results'] = $this->resource::collection($result['results']);
            return $this->successResourceResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
