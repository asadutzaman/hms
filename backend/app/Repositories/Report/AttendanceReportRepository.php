<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * F-13-02 "shift-wise report generated" — per-employee attendance summary
 * (present days, total work hours, average check-in time) grouped by
 * shift, for a date range.
 */
class AttendanceReportRepository extends BaseRepository
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

        $rows = DB::table('attendance_records')
            ->join('employees', 'employees.id', '=', 'attendance_records.employee_id')
            ->leftJoin('shifts', 'shifts.id', '=', 'attendance_records.shift_id')
            ->whereBetween('attendance_records.attendance_date', [$startDate, $endDate])
            ->whereNull('attendance_records.deleted_at')
            ->select(
                'attendance_records.employee_id',
                'employees.name_en as employee_name',
                'attendance_records.shift_id',
                'shifts.name as shift_name',
                DB::raw("COUNT(DISTINCT {$prefix}attendance_records.attendance_date) as present_days"),
                DB::raw("COALESCE(SUM({$prefix}attendance_records.work_hours), 0) as total_work_hours"),
                DB::raw("SUM(CASE WHEN {$prefix}attendance_records.check_in_time IS NULL THEN 1 ELSE 0 END) as missing_check_in_count")
            )
            ->groupBy('attendance_records.employee_id', 'employees.name_en', 'attendance_records.shift_id', 'shifts.name')
            ->orderBy('employees.name_en')
            ->get();

        return [
            'results'    => $rows,
            'summary'    => [
                'total_employees'   => $rows->pluck('employee_id')->unique()->count(),
                'total_present_days' => (int) $rows->sum('present_days'),
                'total_work_hours'  => (float) $rows->sum('total_work_hours'),
            ],
            'date_range' => ['start_date' => $startDate, 'end_date' => $endDate],
        ];
    }
}
