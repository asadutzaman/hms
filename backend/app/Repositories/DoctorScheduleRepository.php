<?php

namespace App\Repositories;

use App\Models\AppointmentSlot;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleException;
use App\Models\DoctorScheduleSlot;
use App\Services\ODataService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DoctorScheduleRepository extends BaseRepository
{
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name', 'schedule_type'];

    public function __construct()
    {
        $this->model = new DoctorSchedule();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /**
     * Default schedule for a doctor (most recent active record).
     */
    public function getDefaultForDoctor(int $doctorId)
    {
        return $this->newQuery()
            ->where('doctor_id', $doctorId)
            ->where('is_default', true)
            ->where('status', 1)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * All weekly recurrence windows for a schedule (Mon..Sun).
     */
    public function getRecurringWindows(int $doctorScheduleId)
    {
        return DoctorScheduleSlot::query()
            ->where('doctor_schedule_id', $doctorScheduleId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Exceptions (leaves / extra slots / blocks) for one date.
     */
    public function getExceptionsForDate(int $doctorId, string $date)
    {
        return DoctorScheduleException::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('exception_date', $date)
            ->where('approval_status', 'approved')
            ->where('status', 1)
            ->get();
    }

    /**
     * Generate (or refresh) appointment_slots rows for the supplied doctor/date.
     *
     * 1. Find the active schedule whose effective window covers the date.
     * 2. Pick matching recurring slot rows (same day_of_week).
     * 3. Apply exception overlays (blocks / extra slots).
     * 4. Insert concrete rows idempotently (unique on doctor/date/start_time).
     */
    public function materializeSlotsForDate(int $doctorId, string $date): array
    {
        $schedule = $this->newQuery()
            ->where('doctor_id', $doctorId)
            ->where('status', 1)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        if (!$schedule) {
            return [];
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0=Sun … 6=Sat
        $windows   = DoctorScheduleSlot::query()
            ->where('doctor_schedule_id', $schedule->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($windows->isEmpty()) {
            return [];
        }

        $exceptions = $this->getExceptionsForDate($doctorId, $date);

        // Pre-block the day if there is a leave/holiday exception with no times.
        $dayBlocked = $exceptions->firstWhere('exception_type', 'leave')
            || $exceptions->firstWhere('exception_type', 'holiday');

        $createdOrUpdated = [];

        foreach ($windows as $window) {
            $slotDuration = $window->slot_duration_minutes ?: $schedule->slot_duration_minutes;
            $maxPatients  = $window->max_patients_per_slot ?: $schedule->max_patients_per_slot;

            $start = Carbon::parse("$date {$window->start_time}");
            $end   = Carbon::parse("$date {$window->end_time}");

            while ($start->copy()->addMinutes($slotDuration)->lte($end)) {
                $slotEnd = $start->copy()->addMinutes($slotDuration);
                $blockReason = null;
                $isBlocked   = (bool) $dayBlocked;

                foreach ($exceptions as $ex) {
                    if ($ex->start_time && $ex->end_time) {
                        $exStart = Carbon::parse("$date {$ex->start_time}");
                        $exEnd   = Carbon::parse("$date {$ex->end_time}");
                        if ($start->lt($exEnd) && $slotEnd->gt($exStart)) {
                            if ($ex->exception_type === 'extra_slot') {
                                // boost capacity rather than block
                                $maxPatients += 1;
                            } else {
                                $isBlocked   = true;
                                $blockReason = $ex->reason;
                            }
                        }
                    }
                }

                $slot = AppointmentSlot::query()->updateOrCreate(
                    [
                        'doctor_id'  => $doctorId,
                        'slot_date'  => $date,
                        'start_time' => $start->format('H:i:s'),
                    ],
                    [
                        'doctor_schedule_id' => $schedule->id,
                        'department_id'      => $schedule->department_id,
                        'chamber_id'         => $schedule->chamber_id,
                        'end_time'           => $slotEnd->format('H:i:s'),
                        'slot_start_at'      => $start,
                        'slot_end_at'        => $slotEnd,
                        'max_patients'       => $maxPatients,
                        'is_blocked'         => $isBlocked,
                        'block_reason'       => $blockReason,
                        'is_extra_slot'      => $exceptions->contains('exception_type', 'extra_slot'),
                        'status'             => $isBlocked ? 'blocked' : 'open',
                        'status_active'      => 1,
                    ]
                );

                $createdOrUpdated[] = $slot;
                $start->addMinutes($slotDuration + (int) $schedule->buffer_minutes);
            }
        }

        return $createdOrUpdated;
    }

    /**
     * Atomically reserve a slot for a doctor/date/time.
     *
     * Returns the AppointmentSlot on success or throws on contention.
     */
    public function reserveSlot(int $doctorId, string $date, string $startTime)
    {
        return DB::transaction(function () use ($doctorId, $date, $startTime) {
            $slot = AppointmentSlot::query()
                ->where('doctor_id', $doctorId)
                ->where('slot_date', $date)
                ->where('start_time', $startTime)
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                // Try to materialize lazily for this date
                $this->materializeSlotsForDate($doctorId, $date);
                $slot = AppointmentSlot::query()
                    ->where('doctor_id', $doctorId)
                    ->where('slot_date', $date)
                    ->where('start_time', $startTime)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$slot) {
                throw new RuntimeException('Selected slot does not exist for this doctor.');
            }

            if ($slot->is_blocked || $slot->status !== 'open') {
                throw new RuntimeException('Selected slot is not available.');
            }

            if ($slot->booked_count + $slot->walk_in_count >= $slot->max_patients) {
                throw new RuntimeException('Selected slot is fully booked.');
            }

            $slot->booked_count = $slot->booked_count + 1;
            if ($slot->booked_count + $slot->walk_in_count >= $slot->max_patients) {
                $slot->status = 'full';
            }
            $slot->save();

            return $slot;
        });
    }

    public function releaseSlot(int $appointmentSlotId)
    {
        if (!$appointmentSlotId) {
            return;
        }

        DB::transaction(function () use ($appointmentSlotId) {
            $slot = AppointmentSlot::query()->lockForUpdate()->find($appointmentSlotId);
            if (!$slot) {
                return;
            }
            $slot->booked_count = max(0, $slot->booked_count - 1);
            if ($slot->status === 'full' && $slot->booked_count + $slot->walk_in_count < $slot->max_patients) {
                $slot->status = 'open';
            }
            $slot->save();
        });
    }
}
