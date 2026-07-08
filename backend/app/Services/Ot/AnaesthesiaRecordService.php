<?php

namespace App\Services\Ot;

use App\Models\AnaesthesiaRecord;
use App\Models\AnaesthesiaRecordEntry;
use App\Models\OtBooking;
use Illuminate\Support\Facades\DB;

class AnaesthesiaRecordService
{
    public function getOrCreate(int $otBookingId, array $data, int $actorId): AnaesthesiaRecord
    {
        $record = AnaesthesiaRecord::query()->where('ot_booking_id', $otBookingId)->first();
        if ($record) {
            return $record;
        }

        OtBooking::query()->findOrFail($otBookingId);

        return AnaesthesiaRecord::query()->create([
            'ot_booking_id'      => $otBookingId,
            'anaesthetist_id'    => $data['anaesthetist_id'] ?? null,
            'anaesthesia_type'   => $data['anaesthesia_type'] ?? 'general',
            'asa_grade'          => $data['asa_grade'] ?? null,
            'premedication'      => $data['premedication'] ?? null,
            'induction_agent'    => $data['induction_agent'] ?? null,
            'airway_management'  => $data['airway_management'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'started_at'         => now(),
            'created_by'         => $actorId,
        ]);
    }

    public function addEntry(int $anaesthesiaRecordId, array $data, int $actorId): AnaesthesiaRecordEntry
    {
        return DB::transaction(function () use ($anaesthesiaRecordId, $data, $actorId) {
            AnaesthesiaRecord::query()->findOrFail($anaesthesiaRecordId);

            return AnaesthesiaRecordEntry::query()->create([
                'anaesthesia_record_id' => $anaesthesiaRecordId,
                'recorded_at'           => $data['recorded_at'] ?? now(),
                'heart_rate'            => $data['heart_rate'] ?? null,
                'bp_systolic'           => $data['bp_systolic'] ?? null,
                'bp_diastolic'          => $data['bp_diastolic'] ?? null,
                'spo2_pct'              => $data['spo2_pct'] ?? null,
                'respiratory_rate'      => $data['respiratory_rate'] ?? null,
                'agent_name'            => $data['agent_name'] ?? null,
                'agent_dose'            => $data['agent_dose'] ?? null,
                'fluids_given'          => $data['fluids_given'] ?? null,
                'remarks'               => $data['remarks'] ?? null,
                'recorded_by'           => $actorId,
                'created_by'            => $actorId,
            ]);
        });
    }

    public function endRecord(int $anaesthesiaRecordId, ?string $recoveryNotes, int $actorId): AnaesthesiaRecord
    {
        $record = AnaesthesiaRecord::query()->findOrFail($anaesthesiaRecordId);
        $record->ended_at = now();
        if ($recoveryNotes !== null) {
            $record->recovery_notes = $recoveryNotes;
        }
        $record->updated_by = $actorId;
        $record->save();
        return $record->fresh('entries');
    }
}
