<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\Report\AttendanceReportRepository;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;

class AttendanceReportController extends Controller
{
    use TraitRestResponse;

    public function __construct(private AttendanceReportRepository $repository)
    {
    }

    public function getReport()
    {
        try {
            $data = $this->repository->init()->getReport();
            return $this->successResponse($data);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
