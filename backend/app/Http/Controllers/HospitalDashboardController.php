<?php

namespace App\Http\Controllers;

use App\Repositories\HospitalDashboardRepository;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;

class HospitalDashboardController extends Controller
{
    use TraitRestResponse;

    private $repository;

    public function __construct(HospitalDashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    /** GET /hospital-dashboard/summary?start_date=&end_date= */
    public function getSummary()
    {
        try {
            $result = $this->repository->init()->getSummary();
            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
