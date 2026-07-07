<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\Report\LabRevenueAnalyticsRepository;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;

class LabRevenueAnalyticsController extends Controller
{
    use TraitRestResponse;

    private $repository;

    public function __construct(LabRevenueAnalyticsRepository $repository)
    {
        $this->repository = $repository;
    }

    /** GET /report/lab-revenue-analytics?start_date=&end_date=&tat_target_hours= */
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
