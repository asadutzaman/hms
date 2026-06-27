<?php

namespace App\Repositories;

use App\Models\DoctorScheduleSlot;

class DoctorScheduleSlotRepository extends BaseRepository
{
    public function __construct(DoctorScheduleSlot $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all slots for a given schedule.
     */
    public function getByScheduleId(int $scheduleId)
    {
        return $this->model
            ->where('schedule_id', $scheduleId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get slots for a schedule on a specific day of week.
     */
    public function getByScheduleAndDay(int $scheduleId, int $dayOfWeek)
    {
        return $this->model
            ->where('schedule_id', $scheduleId)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();
    }
}