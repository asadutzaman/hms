<?php

namespace App\Http\Controllers\PatientPortal;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpdPrescriptionResource;
use App\Models\OpdPrescription;
use App\Services\Opd\OpdPrescriptionService;
use App\Services\PatientSessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

/** F-17-02 View Prescriptions Online — read-only, scoped to the authenticated patient. */
class PatientPortalPrescriptionController extends Controller
{
    use TraitRestResponse;

    /** GET /patient-portal/prescriptions */
    public function index(Request $request)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $rows = OpdPrescription::query()
                ->where('patient_id', $patientId)
                ->with(['items', 'visit.doctor', 'visit.department'])
                ->orderByDesc('created_at')
                ->get();

            $response = OpdPrescriptionResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/prescriptions/{id}/pdf — only own prescription; does not flip the printed flag. */
    public function pdf($id)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $prescription = OpdPrescription::query()->where('patient_id', $patientId)->find($id);
            if (!$prescription) {
                return response()->json(['message' => 'Prescription not found.'], 404);
            }

            return app(OpdPrescriptionService::class)->renderPdf((int) $id, $patientId, false);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
