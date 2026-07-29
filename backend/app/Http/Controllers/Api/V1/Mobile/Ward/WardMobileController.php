<?php

namespace App\Http\Controllers\Api\V1\Mobile\Ward;

use App\Http\Controllers\Api\V1\Mobile\BaseMobileController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\IpdAdmissionController;
use App\Http\Controllers\IpdDischargeSummaryController;
use App\Http\Controllers\IpdFluidBalanceController;
use App\Http\Controllers\IpdMedicationOrderController;
use App\Http\Controllers\IpdVitalController;
use App\Http\Controllers\LabOrderController;
use App\Http\Controllers\PrescriptionDispenseController;
use App\Http\Controllers\RadiologyOrderController;
use App\Models\DailyReview;
use App\Models\IpdAdmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ward-doctor (RMO) app BFF  —  prefix /api/v1/mobile/ward
 * (authVerify + mobile.role:Doctor). Reuses the IPD controllers for the
 * inpatient record, drug chart, orders, vitals/fluids and discharge, and adds
 * the net-new Daily review (WD2). Round list is a light aggregation.
 */
class WardMobileController extends BaseMobileController
{
    private function payload(JsonResponse $response)
    {
        return $response->getData(true);
    }

    // ---- WD0/WD1 RMO dashboard + ward round list ---------------------------

    /** GET /round-list?ward_id= — current inpatients (not yet discharged). */
    public function roundList(Request $request)
    {
        $query = IpdAdmission::query()
            ->where('status', 1)
            ->whereNull('discharge_date')
            ->with(['patient:id,first_name,last_name,mrn,gender,date_of_birth', 'ward:id,name', 'bed:id,bed_number,ward_id']);

        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->integer('ward_id'));
        }

        $rows = $query->orderBy('ward_id')->orderBy('bed_id')->get();
        return $this->mobileSuccess($rows, 'OK', ['total' => $rows->count()]);
    }

    public function dashboard()
    {
        $admitted = IpdAdmission::query()->where('status', 1)->whereNull('discharge_date')->count();
        return $this->mobileSuccess([
            'inpatients'    => $admitted,
            'bed_board'     => $this->payload(app(BedController::class)->board()),
        ]);
    }

    public function admission($id)
    {
        return $this->mobileSuccess($this->payload(app(IpdAdmissionController::class)->show($id)));
    }

    // ---- WD3 Drug chart -----------------------------------------------------

    public function drugChart(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdMedicationOrderController::class)->byAdmission($request, $admissionId)));
    }

    public function orderDrug(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(IpdMedicationOrderController::class)->store($request)), 'Medication ordered.', [], 201);
    }

    public function discontinueDrug(Request $request, $id)
    {
        return $this->mobileSuccess($this->payload(app(IpdMedicationOrderController::class)->discontinue($request, $id)), 'Discontinued.');
    }

    // ---- WD4 Order investigations ------------------------------------------

    public function labOrders(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(LabOrderController::class)->byIpdAdmission($request, $admissionId)));
    }

    public function createLabOrder(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(LabOrderController::class)->store($request)), 'Lab order placed.', [], 201);
    }

    public function radiologyOrders(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(RadiologyOrderController::class)->byIpdAdmission($request, $admissionId)));
    }

    public function createRadiologyOrder(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(RadiologyOrderController::class)->store($request)), 'Radiology order placed.', [], 201);
    }

    // ---- WD5 Results inbox --------------------------------------------------

    public function resultsInbox(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(LabOrderController::class)->worklist($request)));
    }

    // ---- WD6/WD7 Discharge readiness + summary -----------------------------

    public function dischargeSummary(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdDischargeSummaryController::class)->byAdmission($request, $admissionId)));
    }

    public function generateDischargeSummary(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdDischargeSummaryController::class)->generate($request, $admissionId)), 'Draft generated.');
    }

    public function signDischargeSummary(Request $request, $id)
    {
        return $this->mobileSuccess($this->payload(app(IpdDischargeSummaryController::class)->sign($request, $id)), 'Signed.');
    }

    // ---- WD8 Take-home medicines -------------------------------------------

    public function takeHomeMeds($prescriptionId)
    {
        return $this->mobileSuccess($this->payload(app(PrescriptionDispenseController::class)->forPrescription($prescriptionId)));
    }

    // ---- WD9 Inpatient record: vitals + fluids -----------------------------

    public function vitals(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdVitalController::class)->byAdmission($request, $admissionId)));
    }

    public function fluidBalance(Request $request, $admissionId)
    {
        return $this->mobileSuccess($this->payload(app(IpdFluidBalanceController::class)->summary($request, $admissionId)));
    }

    // ---- Patient transfer (reuse) ------------------------------------------

    public function transfer(Request $request, $id)
    {
        return $this->mobileSuccess($this->payload(app(IpdAdmissionController::class)->transferBed($request, $id)), 'Transferred.');
    }

    // ---- WD2 Daily review (net-new) ----------------------------------------

    public function dailyReviews(Request $request, $admissionId)
    {
        $rows = DailyReview::query()
            ->where('status', 1)
            ->where('ipd_admission_id', $admissionId)
            ->orderByDesc('review_date')->orderByDesc('id')
            ->get();
        return $this->mobileSuccess($rows);
    }

    public function storeDailyReview(Request $request, $admissionId)
    {
        $data = $request->validate([
            'progress_note' => ['nullable', 'string'],
            'assessment'    => ['nullable', 'string'],
            'plan'          => ['nullable', 'string'],
            'obs_snapshot'  => ['nullable', 'array'],
            'review_date'   => ['nullable', 'date'],
        ]);
        $data['ipd_admission_id'] = (int) $admissionId;
        $data['author_user_id']   = $this->currentUserId();
        $data['created_by']       = $this->currentUserId();
        $data['review_date']      = $data['review_date'] ?? now()->toDateString();

        $review = DailyReview::create($data);
        return $this->mobileSuccess($review->fresh(), 'Daily review saved.', [], 201);
    }
}
