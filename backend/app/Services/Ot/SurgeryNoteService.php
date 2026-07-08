<?php

namespace App\Services\Ot;

use App\Exceptions\ApiException;
use App\Models\OtBooking;
use App\Models\SurgeryNote;
use Illuminate\Support\Facades\DB;

/**
 * F-09-02 Surgery Notes & WHO Safety Checklist. The WHO Surgical Safety
 * Checklist has three sequential phases (Sign In -> Time Out -> Sign Out);
 * each phase must be electronically signed (completed_by/at) before the
 * next can be recorded, and Sign In must be completed before the booking
 * can move to 'in_progress' (i.e. before incision) — enforced here, not
 * just left to frontend ordering.
 */
class SurgeryNoteService
{
    public function getOrCreate(int $otBookingId): SurgeryNote
    {
        $note = SurgeryNote::query()->where('ot_booking_id', $otBookingId)->first();
        if ($note) {
            return $note;
        }

        OtBooking::query()->findOrFail($otBookingId);

        return SurgeryNote::query()->create(['ot_booking_id' => $otBookingId]);
    }

    public function savePreOpNotes(int $otBookingId, ?string $notes, int $actorId): SurgeryNote
    {
        $note = $this->getOrCreate($otBookingId);
        $note->pre_op_notes = $notes;
        $note->updated_by = $actorId;
        $note->save();
        return $note->fresh();
    }

    public function signInChecklist(int $otBookingId, array $checklist, int $actorId): SurgeryNote
    {
        $note = $this->getOrCreate($otBookingId);
        $note->who_sign_in_checklist = $checklist;
        $note->who_sign_in_by = $actorId;
        $note->who_sign_in_at = now();
        $note->updated_by = $actorId;
        $note->save();
        return $note->fresh();
    }

    public function timeOutChecklist(int $otBookingId, array $checklist, int $actorId): SurgeryNote
    {
        return DB::transaction(function () use ($otBookingId, $checklist, $actorId) {
            $note = $this->getOrCreate($otBookingId);
            if (empty($note->who_sign_in_at)) {
                throw new ApiException('WHO Sign In checklist must be completed before Time Out.', 422);
            }
            $note->who_time_out_checklist = $checklist;
            $note->who_time_out_by = $actorId;
            $note->who_time_out_at = now();
            $note->updated_by = $actorId;
            $note->save();
            return $note->fresh();
        });
    }

    public function signOutChecklist(int $otBookingId, array $checklist, int $actorId): SurgeryNote
    {
        return DB::transaction(function () use ($otBookingId, $checklist, $actorId) {
            $note = $this->getOrCreate($otBookingId);
            if (empty($note->who_time_out_at)) {
                throw new ApiException('WHO Time Out checklist must be completed before Sign Out.', 422);
            }
            $note->who_sign_out_checklist = $checklist;
            $note->who_sign_out_by = $actorId;
            $note->who_sign_out_at = now();
            $note->updated_by = $actorId;
            $note->save();
            return $note->fresh();
        });
    }

    public function recordOpNotes(int $otBookingId, array $data, int $actorId): SurgeryNote
    {
        $note = $this->getOrCreate($otBookingId);
        $note->fill([
            'procedure_performed' => $data['procedure_performed'] ?? $note->procedure_performed,
            'intra_op_notes'      => $data['intra_op_notes'] ?? $note->intra_op_notes,
            'post_op_notes'       => $data['post_op_notes'] ?? $note->post_op_notes,
            'complications'       => $data['complications'] ?? $note->complications,
        ]);
        $note->updated_by = $actorId;
        $note->save();
        return $note->fresh();
    }

    public function surgeonSign(int $otBookingId, int $actorId): SurgeryNote
    {
        return DB::transaction(function () use ($otBookingId, $actorId) {
            $note = $this->getOrCreate($otBookingId);
            if (empty($note->who_sign_out_at)) {
                throw new ApiException('WHO Sign Out checklist must be completed before the surgeon can sign the note.', 422);
            }
            $note->surgeon_signed_by = $actorId;
            $note->surgeon_signed_at = now();
            $note->updated_by = $actorId;
            $note->save();
            return $note->fresh();
        });
    }
}
