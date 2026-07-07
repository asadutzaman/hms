<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * F-14-05 Doctor Revenue & Productivity Report — per-doctor OPD visit
 * count + revenue and IPD admission count + revenue for a date range.
 * "Month-on-month comparison" (per the backlog acceptance criteria) is a
 * frontend concern — call this endpoint twice with two date ranges and
 * diff client-side, rather than the backend baking in a fixed comparison
 * window.
 */
class DoctorProductivityReportRepository extends BaseRepository
{
    protected $request;

    public function init()
    {
        $this->request = request();
        return $this;
    }

    public function getReport(): array
    {
        $startDate = $this->request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $this->request->query('end_date', Carbon::now()->toDateString());
        $prefix = DB::connection()->getTablePrefix() ?? '';

        $opd = DB::table('opd_visits')
            ->leftJoin('employees', 'employees.id', '=', 'opd_visits.doctor_id')
            ->leftJoin('opd_bills', 'opd_bills.opd_visit_id', '=', 'opd_visits.id')
            ->whereBetween(DB::raw("DATE({$prefix}opd_visits.visit_date)"), [$startDate, $endDate])
            ->whereNull('opd_visits.deleted_at')
            ->select(
                'opd_visits.doctor_id',
                'employees.name_en as doctor_name',
                DB::raw("COUNT(DISTINCT {$prefix}opd_visits.id) as opd_visit_count"),
                DB::raw("COALESCE(SUM({$prefix}opd_bills.total), 0) as opd_revenue")
            )
            ->groupBy('opd_visits.doctor_id', 'employees.name_en')
            ->get()
            ->keyBy('doctor_id');

        $ipd = DB::table('ipd_admissions')
            ->leftJoin('employees', 'employees.id', '=', 'ipd_admissions.attending_doctor_id')
            ->leftJoin('ipd_bills', 'ipd_bills.admission_id', '=', 'ipd_admissions.id')
            ->whereBetween(DB::raw("DATE({$prefix}ipd_admissions.admission_date)"), [$startDate, $endDate])
            ->whereNull('ipd_admissions.deleted_at')
            ->select(
                'ipd_admissions.attending_doctor_id as doctor_id',
                'employees.name_en as doctor_name',
                DB::raw("COUNT(DISTINCT {$prefix}ipd_admissions.id) as ipd_admission_count"),
                DB::raw("COALESCE(SUM({$prefix}ipd_bills.total), 0) as ipd_revenue")
            )
            ->groupBy('ipd_admissions.attending_doctor_id', 'employees.name_en')
            ->get()
            ->keyBy('doctor_id');

        $doctorIds = $opd->keys()->merge($ipd->keys())->filter()->unique();

        $results = $doctorIds->map(function ($doctorId) use ($opd, $ipd) {
            $opdRow = $opd->get($doctorId);
            $ipdRow = $ipd->get($doctorId);

            return [
                'doctor_id'           => $doctorId,
                'doctor_name'         => $opdRow->doctor_name ?? $ipdRow->doctor_name ?? '-',
                'opd_visit_count'     => (int) ($opdRow->opd_visit_count ?? 0),
                'opd_revenue'         => (float) ($opdRow->opd_revenue ?? 0),
                'ipd_admission_count' => (int) ($ipdRow->ipd_admission_count ?? 0),
                'ipd_revenue'         => (float) ($ipdRow->ipd_revenue ?? 0),
                'total_revenue'       => (float) ($opdRow->opd_revenue ?? 0) + (float) ($ipdRow->ipd_revenue ?? 0),
            ];
        })->sortByDesc('total_revenue')->values();

        return [
            'results' => $results,
            'summary' => [
                'total_opd_visits'     => (int) $results->sum('opd_visit_count'),
                'total_ipd_admissions' => (int) $results->sum('ipd_admission_count'),
                'total_revenue'        => (float) $results->sum('total_revenue'),
            ],
            'date_range' => ['start_date' => $startDate, 'end_date' => $endDate],
        ];
    }
}
