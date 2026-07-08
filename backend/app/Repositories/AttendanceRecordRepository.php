<?php

namespace App\Repositories;

use App\Models\AttendanceRecord;

class AttendanceRecordRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = ['source', 'remarks'];

    public function __construct(AttendanceRecord $model)
    {
        $this->model = $model;
    }

    public function forEmployeeAndDate(int $employeeId, string $date)
    {
        return $this->newQuery()
            ->where('employee_id', $employeeId)
            ->where('attendance_date', $date)
            ->first();
    }

    public function forEmployeeRange(int $employeeId, string $startDate, string $endDate)
    {
        return $this->newQuery()
            ->with(['shift'])
            ->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date')
            ->get();
    }
}
