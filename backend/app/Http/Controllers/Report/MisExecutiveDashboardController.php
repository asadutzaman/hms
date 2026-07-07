<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\Report\MisExecutiveDashboardRepository;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;

class MisExecutiveDashboardController extends Controller
{
    use TraitRestResponse;

    private $repository;

    public function __construct(MisExecutiveDashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    /** GET /report/mis-dashboard?start_date=&end_date= */
    public function getDashboard()
    {
        try {
            $result = $this->repository->init()->getDashboard();
            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
