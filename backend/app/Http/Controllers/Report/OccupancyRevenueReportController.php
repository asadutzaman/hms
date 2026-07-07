<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\Report\OccupancyRevenueReportRepository;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;

class OccupancyRevenueReportController extends Controller
{
    use TraitRestResponse;

    private $repository;

    public function __construct(OccupancyRevenueReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /** GET /report/occupancy-revenue?start_date=&end_date= */
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
