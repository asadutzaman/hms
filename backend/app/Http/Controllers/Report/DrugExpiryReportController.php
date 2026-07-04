<?php

namespace App\Http\Controllers\Report;

use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Exports\DrugExpiryReportExport;
use App\Validators\DrugExpiryReportValidator;
use App\Repositories\Report\DrugExpiryReportRepository;
use App\Http\Resources\Report\DrugExpiryReportResource;
use App\Traits\Controller\RestControllerTrait;

class DrugExpiryReportController extends Controller
{
    use RestControllerTrait;

    private $repository;
    private $validator;
    private $resource;

    public function __construct(DrugExpiryReportRepository $repository, DrugExpiryReportValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = DrugExpiryReportResource::class;
    }

    public function getExpiryList()
    {
        try {
            $result = ($this->repository->init())->getExpiryList();
            $result['results'] = isset($this->resource) ? $this->resource::collection($result['results']) : $result['results'];
            return $this->successResourceResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function getExpiryListExport()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        return Excel::download(new DrugExpiryReportExport, 'drugExpiryReport.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
