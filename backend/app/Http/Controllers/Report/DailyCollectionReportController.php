<?php

namespace App\Http\Controllers\Report;

use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Exports\DailyCollectionReportExport;
use App\Validators\DailyCollectionReportValidator;
use App\Repositories\Report\DailyCollectionReportRepository;
use App\Http\Resources\Report\DailyCollectionReportResource;
use App\Traits\Controller\RestControllerTrait;

class DailyCollectionReportController extends Controller
{
    use RestControllerTrait;

    private $repository;
    private $validator;
    private $resource;

    public function __construct(DailyCollectionReportRepository $repository, DailyCollectionReportValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = DailyCollectionReportResource::class;
    }

    public function getDailyCollectionList()
    {
        try {
            $result = ($this->repository->init())->getDailyCollectionList();
            $result['results'] = isset($this->resource) ? $this->resource::collection($result['results']) : $result['results'];
            return $this->successResourceResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function getDailyCollectionExport()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        return Excel::download(new DailyCollectionReportExport, 'dailyCollectionReport.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
