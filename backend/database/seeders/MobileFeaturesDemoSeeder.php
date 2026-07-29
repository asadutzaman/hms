<?php

namespace Database\Seeders;

use App\Models\AtoeAssessment;
use App\Models\Bleep;
use App\Models\ClinicalJob;
use App\Models\CodeBlueEvent;
use App\Models\DailyReview;
use App\Models\DischargeChecklist;
use App\Models\OrderSet;
use App\Models\Role;
use App\Models\ShiftHandover;
use App\Models\SoapNote;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data for the mobile-app (/api/v1/mobile) net-new tables so every role
 * app returns realistic data. Idempotent: each row is keyed on a stable
 * business field via updateOrCreate (uuid can't be used — the Uuid trait
 * regenerates it on every create). Safe to run repeatedly; truncates nothing.
 *
 *   php artisan db:seed --class=MobileFeaturesDemoSeeder
 *
 * NEVER run bare `php artisan db:seed` (AuthSeeder truncates users/roles).
 */
class MobileFeaturesDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Nurse role + nurse user (so the Nurse app is reachable by a real nurse)
        $nurseRole = Role::firstOrCreate(
            ['name' => 'Nurse'],
            ['code' => 'ROLE_NURSE', 'description' => 'Ward nursing staff', 'status' => 1]
        );

        $nurse = User::firstOrCreate(
            ['email' => 'nurse1@hms.local'],
            [
                'name'              => 'Nurse Navdeep Kaur',
                'first_name'        => 'Navdeep',
                'last_name'         => 'Kaur',
                'phone'             => '01700000091',
                'password'          => 'password', // hashed by the User model boot hook
                'user_type'         => 'SERVICE_PROVIDER',
                'organization_ids'  => ['1'],
                'organogram_ids'    => ['1'],
                'web_access'        => 1,
                'app_access'        => 1,
                'timezone'          => 'UTC',
                'locale'            => 'en',
                'role_ids'          => [(string) $nurseRole->id],
                'status'            => 1,
            ]
        );
        // Ensure the nurse role is attached even if the user already existed.
        if (!in_array((string) $nurseRole->id, (array) $nurse->role_ids, true)) {
            $nurse->role_ids = array_values(array_unique(array_merge((array) $nurse->role_ids, [(string) $nurseRole->id])));
            $nurse->save();
        }

        $doctorId = User::query()->whereJsonContains('role_ids', (string) (Role::where('name', 'Doctor')->value('id') ?: 9))->value('id') ?: 1;
        $nurseId  = $nurse->id;

        // ---- D3 SOAP notes (patient 1)
        foreach ([
            ['assessment' => 'Paroxysmal SVT — stable (demo)', 'subjective' => 'Palpitations on exertion, no syncope', 'objective' => 'HR 92 regular, BP 118/76', 'plan' => 'Continue bisoprolol; Holter in 4 weeks'],
            ['assessment' => 'Dyslipidaemia review (demo)', 'subjective' => 'Asymptomatic', 'objective' => 'LDL 138', 'plan' => 'Continue atorvastatin 20mg; recheck lipids in 3 months'],
        ] as $s) {
            SoapNote::updateOrCreate(
                ['patient_id' => 1, 'assessment' => $s['assessment']],
                array_merge($s, ['author_user_id' => $doctorId, 'noted_at' => now()->subDays(1), 'status' => 1])
            );
        }

        // ---- D6/N9 Code Blue + Rapid Response
        CodeBlueEvent::updateOrCreate(
            ['location' => 'CCU Bed 40 (demo)'],
            ['event_type' => 'code_blue', 'patient_id' => 1, 'ward_id' => 11, 'bed_id' => 40, 'state' => 'active',
             'severity' => 'critical', 'reason' => 'Ventricular tachycardia, pulseless', 'raised_by' => $nurseId, 'raised_at' => now()->subMinutes(6), 'status' => 1]
        );
        CodeBlueEvent::updateOrCreate(
            ['location' => 'General Ward Bay 3 (demo)'],
            ['event_type' => 'rapid_response', 'patient_id' => 2, 'ward_id' => 10, 'state' => 'resolved',
             'severity' => 'urgent', 'reason' => 'SpO2 88% on air', 'responders' => [['user_id' => $doctorId, 'at' => now()->subMinutes(20)->toDateTimeString()]],
             'outcome_notes' => 'O2 started, improved to 96%', 'raised_by' => $nurseId, 'raised_at' => now()->subMinutes(30), 'responded_at' => now()->subMinutes(24), 'resolved_at' => now()->subMinutes(10), 'status' => 1]
        );

        // ---- WD2 Daily reviews (admission 1)
        foreach ([now()->subDay(), now()] as $d) {
            DailyReview::updateOrCreate(
                ['ipd_admission_id' => 1, 'review_date' => $d->toDateString()],
                ['author_user_id' => $doctorId, 'progress_note' => 'Haemodynamically stable overnight, chest clear.',
                 'assessment' => 'Improving', 'plan' => 'Continue current management; step down telemetry tomorrow',
                 'obs_snapshot' => ['hr' => 78, 'bp' => '124/74', 'spo2' => 97, 'temp' => 36.8], 'status' => 1]
            );
        }

        // ---- DD7/N4 Shift handovers
        ShiftHandover::updateOrCreate(
            ['role_type' => 'doctor', 'summary' => 'Night round handover (demo)'],
            ['ward_id' => 11, 'from_user_id' => $doctorId, 'shift_label' => 'Night → Day', 'state' => 'submitted', 'handed_over_at' => now(),
             'items' => [['patient_id' => 1, 'situation' => 'SVT, on telemetry', 'recommendation' => 'Review AM ECG', 'priority' => 'urgent']]]
        );
        ShiftHandover::updateOrCreate(
            ['role_type' => 'nurse', 'summary' => 'Bay 3 nursing handover (demo)'],
            ['ward_id' => 10, 'from_user_id' => $nurseId, 'shift_label' => 'Day → Night', 'state' => 'submitted', 'handed_over_at' => now(),
             'items' => [['patient_id' => 2, 'situation' => 'Recovering post rapid response', 'priority' => 'routine']]]
        );

        // ---- N11 Discharge checklist (admission 1)
        DischargeChecklist::updateOrCreate(
            ['ipd_admission_id' => 1],
            ['state' => 'in_progress', 'items' => [
                ['key' => 'tto', 'label' => 'TTOs dispensed', 'checked' => true],
                ['key' => 'summary', 'label' => 'Discharge summary signed', 'checked' => false],
                ['key' => 'transport', 'label' => 'Transport arranged', 'checked' => false],
                ['key' => 'followup', 'label' => 'Follow-up booked', 'checked' => true],
            ], 'status' => 1]
        );

        // ---- DD2/N5 Clinical jobs (doctor queue + nurse tasks)
        ClinicalJob::updateOrCreate(
            ['title' => 'Review K+ result — Bed 40 (demo)'],
            ['description' => 'Repeat K+ back, was 6.1', 'job_type' => 'review', 'priority' => 'critical', 'patient_id' => 1, 'ward_id' => 11, 'bed_id' => 40,
             'requested_by' => $nurseId, 'role_type' => 'doctor', 'state' => 'open', 'due_at' => now()->addMinutes(15), 'status' => 1]
        );
        ClinicalJob::updateOrCreate(
            ['title' => 'Re-site cannula — Bay 3 (demo)'],
            ['description' => 'Tissued left forearm', 'job_type' => 'cannula', 'priority' => 'routine', 'patient_id' => 2, 'ward_id' => 10,
             'requested_by' => $nurseId, 'role_type' => 'doctor', 'state' => 'open', 'due_at' => now()->addHours(2), 'status' => 1]
        );
        ClinicalJob::updateOrCreate(
            ['title' => 'Hourly neuro obs — Bed 39 (demo)'],
            ['description' => 'GCS chart hourly until midnight', 'job_type' => 'observation', 'priority' => 'urgent', 'patient_id' => 3, 'ward_id' => 11, 'bed_id' => 39,
             'assigned_to' => $nurseId, 'role_type' => 'nurse', 'state' => 'open', 'due_at' => now()->addMinutes(45), 'status' => 1]
        );

        // ---- DD3 Bleeps
        Bleep::updateOrCreate(
            ['message' => 'Patient in CCU spiking temps, please review (demo)'],
            ['from_user_id' => $nurseId, 'to_user_id' => null, 'patient_id' => 1, 'ward_id' => 11, 'callback' => 'x2214', 'priority' => 'urgent', 'state' => 'sent', 'status' => 1]
        );
        Bleep::updateOrCreate(
            ['message' => 'Crash call — Bay 3 (demo)'],
            ['from_user_id' => $nurseId, 'to_user_id' => $doctorId, 'patient_id' => 2, 'ward_id' => 10, 'callback' => '2222', 'priority' => 'crash', 'state' => 'acknowledged', 'acknowledged_at' => now()->subMinutes(2), 'status' => 1]
        );

        // ---- DD4 A-to-E assessments (patient 1)
        AtoeAssessment::updateOrCreate(
            ['patient_id' => 1, 'impression' => 'Sepsis — likely chest source (demo)'],
            ['ipd_admission_id' => 1, 'assessed_by' => $doctorId, 'assessed_at' => now()->subMinutes(15),
             'airway' => 'Patent, self-maintaining', 'breathing' => 'RR 24, SpO2 93% on 2L, crackles R base',
             'circulation' => 'HR 108, BP 102/64, CRT 3s', 'disability' => 'GCS 15, BM 6.8', 'exposure' => 'Temp 38.6, no rashes',
             'news2_score' => 6, 'plan' => 'Sepsis 6, senior review, IV fluids', 'status' => 1]
        );

        // ---- DD5 Order sets (global)
        OrderSet::updateOrCreate(
            ['name' => 'Sepsis 6'],
            ['category' => 'Emergency', 'description' => 'Sepsis bundle within 1 hour', 'is_global' => true,
             'items' => [
                 ['type' => 'lab', 'ref_id' => 1, 'name' => 'CBC'],
                 ['type' => 'lab', 'ref_id' => 2, 'name' => 'ESR / Lactate'],
                 ['type' => 'medication', 'name' => 'Broad-spectrum antibiotics'],
                 ['type' => 'medication', 'name' => 'IV fluid bolus'],
             ], 'status' => 1]
        );
        OrderSet::updateOrCreate(
            ['name' => 'Chest Pain'],
            ['category' => 'Cardiology', 'description' => 'ACS work-up', 'is_global' => true,
             'items' => [
                 ['type' => 'lab', 'ref_id' => 1, 'name' => 'Troponin / CBC'],
                 ['type' => 'radiology', 'name' => 'Chest X-ray'],
             ], 'status' => 1]
        );

        $this->command?->info('MobileFeaturesDemoSeeder: nurse user nurse1@hms.local (password) + demo rows across 9 mobile tables.');
    }
}
