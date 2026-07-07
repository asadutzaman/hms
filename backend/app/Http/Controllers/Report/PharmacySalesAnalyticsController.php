<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\Report\PharmacySalesAnalyticsRepository;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;

class PharmacySalesAnalyticsController extends Controller
{
    use TraitRestResponse;

    private $repository;

    public function __construct(PharmacySalesAnalyticsRepository $repository)
    {
        $this->repository = $repository;
    }

    /** GET /report/pharmacy-sales-analytics?start_date=&end_date=&limit= */
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
