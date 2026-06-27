<?php

namespace App\Repositories;

use App\Models\DoctorScheduleException;

class DoctorScheduleExceptionRepository extends BaseRepository
{
    public function __construct(DoctorScheduleException $model)
    {
        parent::__construct($model);
    }

    /**
     * Get exceptions for a schedule within a date range.
     */
    public function getByDateRange(int $scheduleId, string $fromDate, string $toDate)
    {
        return $this->model
            ->where('schedule_id', $scheduleId)
            ->whereBetween('exception_date', [$fromDate, $toDate])
            ->orderBy('exception_date')
            ->get();
    }

    /**
     * Get exception for a specific schedule and date (if any).
     */
    public function getByScheduleAndDate(int $scheduleId, string $date)
    {
        return $this->model
            ->where('schedule_id', $scheduleId)
            ->where('exception_date', $date)
            ->first();
    }
}