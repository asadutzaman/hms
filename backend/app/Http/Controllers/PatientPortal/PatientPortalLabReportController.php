<?php

namespace App\Http\Controllers\PatientPortal;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabOrderResource;
use App\Models\LabOrder;
use App\Repositories\LabOrderRepository;
use App\Services\Lis\LabOrderService;
use App\Services\PatientSessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

/**
 * F-17-03 View & Download Lab Reports — read-only, scoped to the
 * authenticated patient. PDF download is gated on order_status being
 * verified/reported by LabOrderService::renderReportPdf() itself (a
 * pathologist must release the report first) — nothing extra to enforce here.
 */
class PatientPortalLabReportController extends Controller
{
    use TraitRestResponse;

    public function __construct(private LabOrderRepository $repository)
    {
    }

    /** GET /patient-portal/lab-reports */
    public function index(Request $request)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();
            $rows = $this->repository->forPatient((int) $patientId);

            $response = LabOrderResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/lab-reports/{id}/pdf — only own order. */
    public function pdf($id)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $order = LabOrder::query()->where('patient_id', $patientId)->find($id);
            if (!$order) {
                return response()->json(['message' => 'Lab order not found.'], 404);
            }

            return app(LabOrderService::class)->renderReportPdf((int) $id);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
