<?php

namespace App\Repositories;

use App\Repositories\Report\ItemLowStockReportRepository;
use App\Repositories\Report\MisExecutiveDashboardRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Composite "hospital operations" dashboard for the admin landing page.
 * Unlike MisExecutiveDashboardRepository (a monthly executive report), this
 * defaults to TODAY — it's a live ops view, not a report. Range-scoped
 * widgets (appointments/OPD/IPD/revenue/radiology/OT/attendance) honour
 * start_date/end_date; point-in-time widgets (bed occupancy, blood
 * inventory, insurance claim tracking, low-stock alerts, lab backlog) are
 * intentionally NOT date-filtered — they reflect current state regardless
 * of the selected range, same as InsuranceClaimRepository::trackingSummary().
 */
class HospitalDashboardRepository extends BaseRepository
{
    protected $request;

    public function __construct(
        protected BedRepository $bedRepository,
        protected InsuranceClaimRepository $insuranceClaimRepository,
        protected BloodUnitRepository $bloodUnitRepository,
        protected ItemLowStockReportRepository $itemLowStockReportRepository,
        protected MisExecutiveDashboardRepository $misExecutiveDashboardRepository,
    ) {
    }

    public function init()
    {
        $this->request = request();
        return $this;
    }

    public function getSummary(): array
    {
        $startDate = $this->request->query('start_date', Carbon::today()->toDateString());
        $endDate   = $this->request->query('end_date', Carbon::today()->toDateString());

        return [
            'date_range'            => ['start_date' => $startDate, 'end_date' => $endDate],
            'clinical_kpis'         => $this->misExecutiveDashboardRepository->init()->getDashboard(),
            'appointments_today'    => $this->getAppointmentsSummary($startDate, $endDate),
            'radiology_today'       => $this->getRadiologySummary($startDate, $endDate),
            'ot_bookings_today'     => $this->getOtBookingSummary($startDate, $endDate),
            'attendance_today'      => $this->getAttendanceSummary($startDate, $endDate),
            'bed_occupancy'         => $this->bedRepository->occupancyDashboard(),
            'insurance_claims'      => $this->insuranceClaimRepository->trackingSummary(),
            'blood_bank_inventory'  => $this->bloodUnitRepository->inventorySummary(),
            'low_stock_alerts'      => $this->itemLowStockReportRepository->getReorderAlerts(),
            'pending_lab_orders'    => $this->getPendingLabOrdersSummary(),
        ];
    }

    protected function getAppointmentsSummary(string $startDate, string $endDate): array
    {
        $rows = DB::table('appointments')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->selectRaw('status, COUNT(*) as appointment_count')
            ->groupBy('status')
            ->get();

        return [
            'total_count'   => (int) $rows->sum('appointment_count'),
            'status_counts' => $rows,
        ];
    }

    protected function getRadiologySummary(string $startDate, string $endDate): array
    {
        $statusCounts = DB::table('radiology_orders')
            ->whereBetween(DB::raw('DATE(ordered_at)'), [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->selectRaw('order_status, COUNT(*) as order_count')
            ->groupBy('order_status')
            ->get();

        // radiology_reports has no direct radiology_order_id column -- it links via
        // radiology_order_item_id -> radiology_order_items.id, which in turn links to
        // radiology_orders via radiology_order_id. Two-hop join required. Postgres has
        // no TIMESTAMPDIFF(); use EXTRACT(EPOCH FROM ...) for an hour delta instead.
        $prefix = DB::connection()->getTablePrefix() ?? '';
        $avgTurnaroundHours = DB::table('radiology_orders')
            ->join('radiology_order_items', 'radiology_order_items.radiology_order_id', '=', 'radiology_orders.id')
            ->join('radiology_reports', 'radiology_reports.radiology_order_item_id', '=', 'radiology_order_items.id')
            ->whereBetween(DB::raw("DATE({$prefix}radiology_reports.reported_at)"), [$startDate, $endDate])
            ->whereNull('radiology_orders.deleted_at')
            ->whereNotNull('radiology_reports.reported_at')
            ->avg(DB::raw("EXTRACT(EPOCH FROM ({$prefix}radiology_reports.reported_at - {$prefix}radiology_orders.ordered_at)) / 3600"));

        return [
            'total_count'          => (int) $statusCounts->sum('order_count'),
            'status_counts'        => $statusCounts,
            'avg_turnaround_hours' => $avgTurnaroundHours !== null ? round((float) $avgTurnaroundHours, 1) : null,
        ];
    }

    protected function getOtBookingSummary(string $startDate, string $endDate): array
    {
        $rows = DB::table('ot_bookings')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->selectRaw('booking_status, COUNT(*) as booking_count')
            ->groupBy('booking_status')
            ->get();

        return [
            'total_count'   => (int) $rows->sum('booking_count'),
            'status_counts' => $rows,
        ];
    }

    protected function getAttendanceSummary(string $startDate, string $endDate): array
    {
        $presentCount = (int) DB::table('attendance_records')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereNotNull('check_in_time')
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(DISTINCT employee_id) as present_count')
            ->value('present_count');

        $totalEmployees = DB::table('employees')->whereNull('deleted_at')->where('status', 1)->count();

        return [
            'total_employees' => $totalEmployees,
            'present_count'   => $presentCount,
            'absent_count'    => max($totalEmployees - $presentCount, 0),
        ];
    }

    protected function getPendingLabOrdersSummary(): array
    {
        $count = DB::table('lab_orders')
            ->whereNotIn('order_status', ['reported', 'cancelled'])
            ->whereNull('deleted_at')
            ->count();

        return ['pending_count' => $count];
    }
}
