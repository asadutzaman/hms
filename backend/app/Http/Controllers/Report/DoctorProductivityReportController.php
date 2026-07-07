<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\Report\DoctorProductivityReportRepository;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;

class DoctorProductivityReportController extends Controller
{
    use TraitRestResponse;

    private $repository;

    public function __construct(DoctorProductivityReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /** GET /report/doctor-productivity?start_date=&end_date= */
    public function getReport()
    {
        try {
            $result = $this->repository->init()->getReport();
            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
