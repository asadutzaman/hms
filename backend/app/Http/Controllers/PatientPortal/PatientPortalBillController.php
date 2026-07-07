<?php

namespace App\Http\Controllers\PatientPortal;

use App\Http\Controllers\Controller;
use App\Http\Resources\IpdBillResource;
use App\Http\Resources\OpdBillResource;
use App\Models\IpdBill;
use App\Models\OpdBill;
use App\Services\Ipd\IpdBillService;
use App\Services\Opd\OpdBillService;
use App\Services\PatientSessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

/** F-17-04 Download Invoices & Receipts — read-only, scoped to the authenticated patient. */
class PatientPortalBillController extends Controller
{
    use TraitRestResponse;

    /** GET /patient-portal/bills/opd */
    public function opdBills(Request $request)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $rows = OpdBill::query()
                ->whereHas('visit', fn ($q) => $q->where('patient_id', $patientId))
                ->with(['visit'])
                ->orderByDesc('billed_at')
                ->get();

            $response = OpdBillResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/bills/ipd */
    public function ipdBills(Request $request)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $rows = IpdBill::query()
                ->whereHas('admission', fn ($q) => $q->where('patient_id', $patientId))
                ->with(['admission'])
                ->orderByDesc('billed_at')
                ->get();

            $response = IpdBillResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/bills/opd/{id}/pdf */
    public function opdBillPdf($id)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $bill = OpdBill::query()->whereHas('visit', fn ($q) => $q->where('patient_id', $patientId))->find($id);
            if (!$bill) {
                return response()->json(['message' => 'Bill not found.'], 404);
            }

            return app(OpdBillService::class)->renderReceiptPdf((int) $id);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/bills/ipd/{id}/pdf */
    public function ipdBillPdf($id)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();

            $bill = IpdBill::query()->whereHas('admission', fn ($q) => $q->where('patient_id', $patientId))->find($id);
            if (!$bill) {
                return response()->json(['message' => 'Bill not found.'], 404);
            }

            return app(IpdBillService::class)->renderReceiptPdf((int) $id);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
