<?php

namespace App\Http\Controllers\Api\V1\Mobile\Admin;

use App\Http\Controllers\Api\V1\Mobile\BaseMobileController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\ErVisitController;
use App\Http\Controllers\HospitalDashboardController;
use App\Http\Controllers\OpdVisitController;
use App\Http\Controllers\PaymentTransactionController;
use App\Http\Controllers\Report\DailyCollectionReportController;
use App\Http\Controllers\Report\DoctorProductivityReportController;
use App\Http\Controllers\Report\MisExecutiveDashboardController;
use App\Http\Controllers\Report\OccupancyRevenueReportController;
use App\Models\IpdAdmission;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Administrator app BFF  —  prefix /api/v1/mobile/admin
 * (authVerify + mobile.role:Super Admin/Administrator). Pure reuse of the
 * existing dashboard / report / live-board controllers.
 */
class AdminMobileController extends BaseMobileController
{
    private function payload(JsonResponse $response)
    {
        return $response->getData(true);
    }

    // ---- A1 Dashboard -------------------------------------------------------

    public function dashboard()
    {
        return $this->mobileSuccess([
            'hospital' => $this->payload(app(HospitalDashboardController::class)->getSummary()),
            'mis'      => $this->payload(app(MisExecutiveDashboardController::class)->getDashboard()),
        ]);
    }

    // ---- A2 Bed occupancy ---------------------------------------------------

    public function bedOccupancy()
    {
        return $this->mobileSuccess([
            'summary' => $this->payload(app(BedController::class)->dashboard()),
            'board'   => $this->payload(app(BedController::class)->board()),
        ]);
    }

    // ---- A3 Live operations -------------------------------------------------

    public function liveOps(Request $request)
    {
        return $this->mobileSuccess([
            'opd_board' => $this->payload(app(OpdVisitController::class)->displayBoard($request)),
            'ed_board'  => $this->payload(app(ErVisitController::class)->board($request)),
            'bed_board' => $this->payload(app(BedController::class)->board()),
        ]);
    }

    // ---- A8/A9/A10 Monitors -------------------------------------------------

    public function opdMonitor(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(OpdVisitController::class)->today($request)));
    }

    public function ipdMonitor()
    {
        $rows = IpdAdmission::query()->where('status', 1)->whereNull('discharge_date')
            ->with(['patient:id,first_name,last_name,mrn', 'ward:id,name', 'bed:id,bed_number'])
            ->orderBy('ward_id')->get();
        return $this->mobileSuccess($rows, 'OK', ['admitted' => $rows->count()]);
    }

    public function emergencyMonitor(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(ErVisitController::class)->board($request)));
    }

    // ---- A5/A11 Finance + billing counters ---------------------------------

    public function finance(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(DailyCollectionReportController::class)->getDailyCollectionList()));
    }

    public function collections(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(PaymentTransactionController::class)->index($request)));
    }

    // ---- A6 Staffing & capacity --------------------------------------------

    public function staffing()
    {
        $pendingLeave = LeaveRequest::query()->where('status', 1)->latest('id')->limit(25)->get();
        return $this->mobileSuccess([
            'occupancy_revenue' => $this->payload(app(OccupancyRevenueReportController::class)->getReport()),
            'recent_leave_requests' => $pendingLeave,
        ]);
    }

    // ---- A7 Reports library -------------------------------------------------

    /** GET /reports — catalog of report endpoints exposed to the mobile admin. */
    public function reports()
    {
        return $this->mobileSuccess([
            ['key' => 'daily-collection', 'title' => 'Daily Collection', 'path' => '/api/v1/mobile/admin/reports/daily-collection'],
            ['key' => 'occupancy-revenue', 'title' => 'Occupancy & Revenue', 'path' => '/api/v1/mobile/admin/reports/occupancy-revenue'],
            ['key' => 'doctor-productivity', 'title' => 'Doctor Productivity', 'path' => '/api/v1/mobile/admin/reports/doctor-productivity'],
            ['key' => 'mis-dashboard', 'title' => 'Executive MIS', 'path' => '/api/v1/mobile/admin/dashboard'],
        ]);
    }

    public function report(Request $request, $key)
    {
        return match ($key) {
            'daily-collection'    => $this->mobileSuccess($this->payload(app(DailyCollectionReportController::class)->getDailyCollectionList())),
            'occupancy-revenue'   => $this->mobileSuccess($this->payload(app(OccupancyRevenueReportController::class)->getReport())),
            'doctor-productivity' => $this->mobileSuccess($this->payload(app(DoctorProductivityReportController::class)->getReport())),
            default               => $this->mobileError('Unknown report key.', 404),
        };
    }
}
