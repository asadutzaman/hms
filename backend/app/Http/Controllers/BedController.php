<?php

namespace App\Http\Controllers;

use App\Validators\BedValidator;
use App\Repositories\BedRepository;
use App\Http\Resources\BedResource;
use App\Traits\Controller\RestControllerTrait;

class BedController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'bed_status'];

    use RestControllerTrait;

    public function __construct(BedRepository $repository, BedValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = BedResource::class;
    }

    /**
     * GET /bed/dashboard — bed occupancy summary for the IPD bed-board.
     */
    public function dashboard()
    {
        try {
            $result = $this->repository->occupancyDashboard();
            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /bed/board — full bed list (ward + current patient) for the
     * visual bed-board grid, grouped by ward on the frontend.
     */
    public function board()
    {
        try {
            $beds = $this->repository->boardBeds();
            $result = $beds->map(function ($bed) {
                $admission = $bed->admissions->first();
                return [
                    'id'             => $bed->id,
                    'ward_id'        => $bed->ward_id,
                    'ward_name'      => optional($bed->ward)->name,
                    'bed_number'     => $bed->bed_number,
                    'bed_type'       => $bed->bed_type,
                    'daily_rate'     => $bed->daily_rate,
                    'bed_status'     => $bed->bed_status,
                    'admission_id'   => $admission->id ?? null,
                    'admission_no'   => $admission->admission_no ?? null,
                    'patient_name'   => $admission
                        ? trim(($admission->patient->first_name ?? '') . ' ' . ($admission->patient->last_name ?? ''))
                        : null,
                ];
            });
            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

}
