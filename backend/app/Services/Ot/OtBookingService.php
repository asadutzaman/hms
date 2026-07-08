<?php

namespace App\Services\Ot;

use App\Enums\OtBookingStatusEnum;
use App\Exceptions\ApiException;
use App\Models\OtBooking;
use App\Models\SurgeryNote;
use App\Repositories\OtBookingRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OtBookingService
{
    protected OtBookingRepository $repository;

    public function __construct(OtBookingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function book(array $data, int $actorId): OtBooking
    {
        return DB::transaction(function () use ($data, $actorId) {
            $this->assertNoOverlap($data['theatre_id'], $data['scheduled_date'], $data['scheduled_start_time'], $data['scheduled_end_time']);

            $booking = OtBooking::query()->create([
                'booking_no'            => $this->generateBookingNo(now()->toDateString()),
                'patient_id'            => $data['patient_id'],
                'ipd_admission_id'      => $data['ipd_admission_id'] ?? null,
                'theatre_id'            => $data['theatre_id'],
                'department_id'         => $data['department_id'] ?? null,
                'surgeon_id'            => $data['surgeon_id'],
                'anaesthetist_id'       => $data['anaesthetist_id'] ?? null,
                'surgery_name'          => $data['surgery_name'],
                'surgery_type'          => $data['surgery_type'] ?? 'elective',
                'scheduled_date'        => $data['scheduled_date'],
                'scheduled_start_time'  => $data['scheduled_start_time'],
                'scheduled_end_time'    => $data['scheduled_end_time'],
                'equipment_list'        => $data['equipment_list'] ?? [],
                'notes'                 => $data['notes'] ?? null,
                'booked_by'             => $actorId,
                'created_by'            => $actorId,
            ]);

            return $booking->fresh(['patient', 'theatre', 'surgeon', 'anaesthetist']);
        });
    }

    public function reschedule(int $id, array $data, int $actorId): OtBooking
    {
        return DB::transaction(function () use ($id, $data, $actorId) {
            $booking = OtBooking::query()->lockForUpdate()->findOrFail($id);

            if (in_array($booking->booking_status, [OtBookingStatusEnum::COMPLETED, OtBookingStatusEnum::CANCELLED], true)) {
                throw new ApiException("Cannot reschedule a booking in status '{$booking->booking_status}'.", 422);
            }

            $theatreId = $data['theatre_id'] ?? $booking->theatre_id;
            $date = $data['scheduled_date'] ?? $booking->scheduled_date->toDateString();
            $start = $data['scheduled_start_time'] ?? $booking->scheduled_start_time;
            $end = $data['scheduled_end_time'] ?? $booking->scheduled_end_time;
            $this->assertNoOverlap($theatreId, $date, $start, $end, $id);

            $booking->fill([
                'theatre_id'           => $theatreId,
                'scheduled_date'       => $date,
                'scheduled_start_time' => $start,
                'scheduled_end_time'   => $end,
                'updated_by'           => $actorId,
            ]);
            $booking->save();

            return $booking->fresh(['patient', 'theatre', 'surgeon', 'anaesthetist']);
        });
    }

    public function cancel(int $id, ?string $reason, int $actorId): OtBooking
    {
        return DB::transaction(function () use ($id, $reason, $actorId) {
            $booking = OtBooking::query()->lockForUpdate()->findOrFail($id);

            if (in_array($booking->booking_status, [OtBookingStatusEnum::COMPLETED, OtBookingStatusEnum::CANCELLED], true)) {
                throw new ApiException("Cannot cancel a booking in status '{$booking->booking_status}'.", 422);
            }

            $booking->booking_status = OtBookingStatusEnum::CANCELLED;
            $booking->cancellation_reason = $reason;
            $booking->updated_by = $actorId;
            $booking->save();

            return $booking->fresh();
        });
    }

    public function startSurgery(int $id, int $actorId): OtBooking
    {
        return DB::transaction(function () use ($id, $actorId) {
            $booking = OtBooking::query()->lockForUpdate()->findOrFail($id);

            if ($booking->booking_status !== OtBookingStatusEnum::SCHEDULED) {
                throw new ApiException("Cannot start surgery from status '{$booking->booking_status}'.", 422);
            }

            // F-09-02: "WHO checklist completed before incision" — enforced
            // here, not just left to frontend ordering.
            $note = SurgeryNote::query()->where('ot_booking_id', $booking->id)->first();
            if (empty($note) || empty($note->who_sign_in_at)) {
                throw new ApiException('The WHO Sign In checklist must be completed before starting surgery.', 422);
            }

            $booking->booking_status = OtBookingStatusEnum::IN_PROGRESS;
            $booking->actual_start_time = now();
            $booking->updated_by = $actorId;
            $booking->save();

            return $booking->fresh();
        });
    }

    public function completeSurgery(int $id, int $actorId): OtBooking
    {
        return DB::transaction(function () use ($id, $actorId) {
            $booking = OtBooking::query()->lockForUpdate()->findOrFail($id);

            if ($booking->booking_status !== OtBookingStatusEnum::IN_PROGRESS) {
                throw new ApiException("Cannot complete surgery from status '{$booking->booking_status}'.", 422);
            }

            $booking->booking_status = OtBookingStatusEnum::COMPLETED;
            $booking->actual_end_time = now();
            $booking->updated_by = $actorId;
            $booking->save();

            return $booking->fresh();
        });
    }

    /** Rejects an overlapping booking in the same theatre (basic double-booking guard). */
    protected function assertNoOverlap(int $theatreId, string $date, string $start, string $end, ?int $excludeId = null): void
    {
        $query = OtBooking::query()
            ->where('theatre_id', $theatreId)
            ->where('scheduled_date', $date)
            ->whereNotIn('booking_status', ['cancelled'])
            ->where('scheduled_start_time', '<', $end)
            ->where('scheduled_end_time', '>', $start);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new ApiException('This theatre already has an overlapping booking for the selected date/time.', 422);
        }
    }

    protected function generateBookingNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'OT_BOOKING')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'OT_BOOKING',
                    'prefix'        => 'OT',
                    'separator'     => '-',
                    'next_sequence' => 2,
                    'sequence_date' => $dateYmd,
                    'status'        => 1,
                    'sort_order'    => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')->where('id', $row->id)->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "OT-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
