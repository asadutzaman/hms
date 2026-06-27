<?php

namespace App\Repositories;

use App\Models\AppointmentSlot;
use Illuminate\Support\Facades\DB;

class AppointmentSlotRepository extends BaseRepository
{
    public function __construct(AppointmentSlot $model)
    {
        parent::__construct($model);
    }

    /**
     * Get slots for a given doctor on a given date (booked_count < max_capacity).
     */
    public function getAvailableSlotsByDoctorAndDate(int $doctorId, string $date)
    {
        return $this->model
            ->where('doctor_id', $doctorId)
            ->where('slot_date', $date)
            ->whereRaw('booked_count < max_capacity')
            ->where('status', 1)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get all slots for a doctor in a date range.
     */
    public function getByDoctorAndDateRange(int $doctorId, string $fromDate, string $toDate)
    {
        return $this->model
            ->where('doctor_id', $doctorId)
            ->whereBetween('slot_date', [$fromDate, $toDate])
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Decrement available capacity by 1 atomically. Returns updated slot or null if no capacity.
     */
    public function decrementCapacity(int $slotId)
    {
        return DB::transaction(function () use ($slotId) {
            $slot = $this->model->where('id', $slotId)->lockForUpdate()->first();
            if (!$slot) {
                return null;
            }
            if ($slot->booked_count >= $slot->max_capacity) {
                return null;
            }
            $slot->booked_count = $slot->booked_count + 1;
            $slot->save();
            return $slot;
        });
    }

    /**
     * Increment booked_count back (used on cancel/no-show).
     */
    public function incrementCapacity(int $slotId)
    {
        return DB::transaction(function () use ($slotId) {
            $slot = $this->model->where('id', $slotId)->lockForUpdate()->first();
            if (!$slot) {
                return null;
            }
            if ($slot->booked_count > 0) {
                $slot->booked_count = $slot->booked_count - 1;
                $slot->save();
            }
            return $slot;
        });
    }

    /**
     * Get slot by doctor + date + start_time (used when materializing booking).
     */
    public function findSlot(int $doctorId, string $date, string $startTime)
    {
        return $this->model
            ->where('doctor_id', $doctorId)
            ->where('slot_date', $date)
            ->where('start_time', $startTime)
            ->first();
    }
}