<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * F-14-02 MIS Executive Dashboard — top-level KPIs (OPD/IPD/Revenue/
 * Occupancy) plus a department-level drill-down, for a date range
 * (defaults to the current month, matching "refreshes hourly" being a
 * frontend polling concern, not a backend caching concern for this sprint).
 */
class MisExecutiveDashboardRepository extends BaseRepository
{
    protected $request;

    public function init()
    {
        $this->request = request();
        return $this;
    }

    public function getDashboard(): array
    {
        $startDate = $this->request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $this->request->query('end_date', Carbon::now()->toDateString());

        return [
            'kpis'                 => $this->getKpis($startDate, $endDate),
            'department_breakdown' => $this->getDepartmentBreakdown($startDate, $endDate),
            'date_range'           => ['start_date' => $startDate, 'end_date' => $endDate],
        ];
    }

    protected function getKpis(string $startDate, string $endDate): array
    {
        $opdVisitCount = DB::table('opd_visits')
            ->whereBetween(DB::raw('DATE(visit_date)'), [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->count();

        $opdRevenue = (float) DB::table('opd_bill_payments')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->sum('amount');

        $ipdAdmissionCount = DB::table('ipd_admissions')
            ->whereBetween(DB::raw('DATE(admission_date)'), [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->count();

        $ipdRevenue = (float) DB::table('ipd_bill_payments')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->sum('amount');

        $totalBeds = DB::table('beds')->whereNull('deleted_at')->count();
        $occupiedBeds = DB::table('beds')->whereNull('deleted_at')->where('bed_status', 'occupied')->count();
        $occupancyPercent = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0.0;

        return [
            'opd_visit_count'      => $opdVisitCount,
            'opd_revenue'          => round($opdRevenue, 2),
            'ipd_admission_count'  => $ipdAdmissionCount,
            'ipd_revenue'          => round($ipdRevenue, 2),
            'total_revenue'        => round($opdRevenue + $ipdRevenue, 2),
            'total_beds'           => $totalBeds,
            'occupied_beds'        => $occupiedBeds,
            'occupancy_percent'    => $occupancyPercent,
        ];
    }

    protected function getDepartmentBreakdown(string $startDate, string $endDate)
    {
        $prefix = DB::connection()->getTablePrefix() ?? '';

        return DB::table('opd_visits')
            ->leftJoin('departments', 'departments.id', '=', 'opd_visits.department_id')
            ->leftJoin('opd_bills', 'opd_bills.opd_visit_id', '=', 'opd_visits.id')
            ->whereBetween(DB::raw("DATE({$prefix}opd_visits.visit_date)"), [$startDate, $endDate])
            ->whereNull('opd_visits.deleted_at')
            ->select(
                'opd_visits.department_id',
                'departments.name as department_name',
                DB::raw("COUNT(DISTINCT {$prefix}opd_visits.id) as visit_count"),
                DB::raw("COALESCE(SUM({$prefix}opd_bills.total), 0) as revenue")
            )
            ->groupBy('opd_visits.department_id', 'departments.name')
            ->orderByDesc('revenue')
            ->get();
    }
}
