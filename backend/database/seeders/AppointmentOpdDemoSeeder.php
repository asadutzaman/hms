<?php

namespace Database\Seeders;

use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Models\Appointment;
use App\Models\AppointmentWaitlist;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OpdDiagnosis;
use App\Models\OpdVisit;
use App\Models\OpdVital;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\User;
use App\Repositories\OpdVisitRepository;
use App\Services\Opd\OpdBillService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Volume for the appointment book and OPD worklist: ~40 appointments spread
 * across doctors/dates/statuses, a handful of waitlist entries, ~14 more OPD
 * visits at varying lifecycle stages (some just waiting, most billed/paid),
 * and patient allergy flags. Builds on the single doctor/patient seeded by
 * OpdDemoSeeder plus the doctor roster from MasterDataDemoSeeder.
 *
 * Idempotent: appointments keyed by `appointment_no`, OPD visits keyed by
 * `opd_no` (date-scoped, so re-running on the same day is a no-op for that
 * day but adds nothing new — safe).
 */
class AppointmentOpdDemoSeeder extends Seeder
{
    private array $doctorEmployeeIds = [];
    // Appointments/waitlists key doctor_id to users; OPD visits still key it to
    // employees. Keep both id spaces so each writes the right one.
    private array $doctorUserIds = [];
    private array $patientIds = [];
    private int $receptionistId;
    private int $actorId;

    public function run(): void
    {
        $this->command->info('[AppointmentOpdDemoSeeder] Starting ...');

        $this->doctorEmployeeIds = Employee::query()->where('employee_type', 'doctor')->pluck('id')->all();
        $this->doctorUserIds     = Employee::query()->where('employee_type', 'doctor')
            ->whereNotNull('user_id')->pluck('user_id')->all();
        $this->patientIds        = Patient::query()->pluck('id')->all();
        $this->receptionistId    = User::query()->where('email', 'reception@hms.local')->value('id') ?? 1;
        $this->actorId           = User::query()->where('email', 'doctor@hms.local')->value('id') ?? 1;

        $this->seedAppointments();
        $this->seedWaitlist();
        $this->seedOpdVisits();
        $this->seedAllergies();

        $this->command->info('[AppointmentOpdDemoSeeder] Done.');
    }

    private function pickDoctor(int $i): int
    {
        return $this->doctorEmployeeIds[$i % count($this->doctorEmployeeIds)];
    }

    /** Doctor as a user id — for appointments/waitlists (FK -> users). */
    private function pickDoctorUser(int $i): int
    {
        return $this->doctorUserIds[$i % count($this->doctorUserIds)];
    }

    private function pickPatient(int $i): int
    {
        return $this->patientIds[$i % count($this->patientIds)];
    }

    private function seedAppointments(): void
    {
        $statuses = ['confirmed', 'completed', 'completed', 'checked_in', 'cancelled', 'no_show', 'pending', 'confirmed'];
        $sources  = ['online', 'walk_in', 'phone', 'staff'];
        $count = 0;

        for ($i = 1; $i <= 40; $i++) {
            $apptNo = 'APT-DEMO-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT);
            if (Appointment::query()->where('appointment_no', $apptNo)->exists()) {
                continue;
            }

            $dayOffset = rand(-20, 10);
            $date = Carbon::today()->addDays($dayOffset);
            $doctorId = $this->pickDoctorUser($i); // appointments.doctor_id -> users
            $deptId = Department::query()->where('name', '!=', 'General OPD')->inRandomOrder()->value('id');

            $status = $dayOffset < 0 ? $statuses[$i % count($statuses)] : 'confirmed';
            $paymentStatus = in_array($status, ['completed'], true) ? 'paid' : 'unpaid';

            Appointment::query()->forceCreate([
                'appointment_no' => $apptNo,
                'patient_id' => $this->pickPatient($i),
                'doctor_id' => $doctorId,
                'department_id' => $deptId,
                'source' => $sources[$i % count($sources)],
                'consultation_mode' => 'in_person',
                'appointment_date' => $date->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '09:15:00',
                'appointment_at' => $date->copy()->setTime(9, 0),
                'token_number' => ($i % 20) + 1,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'consultation_fee' => 800,
                'paid_amount' => $paymentStatus === 'paid' ? 800 : 0,
                'currency' => 'BDT',
                'reason_for_visit' => 'General consultation',
                'booked_by' => $this->receptionistId,
                'created_by' => $this->receptionistId,
                'updated_by' => $this->receptionistId,
                'status_active' => 1,
                'sort_order' => 0,
            ]);
            $count++;
        }

        $this->command->info("[AppointmentOpdDemoSeeder] Appointments created: {$count}");
    }

    private function seedWaitlist(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $doctorId = $this->pickDoctorUser($i + 3); // appointment_waitlists.doctor_id -> users
            $patientId = $this->pickPatient($i + 5);

            $exists = DB::table('appointment_waitlists')
                ->where(['patient_id' => $patientId, 'doctor_id' => $doctorId])
                ->exists();
            if ($exists) {
                continue;
            }

            AppointmentWaitlist::query()->forceCreate([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'preferred_date_from' => Carbon::today()->toDateString(),
                'preferred_date_to' => Carbon::today()->addDays(7)->toDateString(),
                'time_preference' => 'any',
                'priority' => rand(1, 5),
                'queue_position' => $i,
                'status' => $i % 3 === 0 ? 'converted' : 'waiting',
                'reason_for_visit' => 'Follow-up consultation',
                'created_by' => $this->receptionistId,
                'updated_by' => $this->receptionistId,
                'status_active' => 1,
                'sort_order' => 0,
            ]);
        }
    }

    private function seedOpdVisits(): void
    {
        $visitRepo = app(OpdVisitRepository::class);
        $billService = app(OpdBillService::class);

        // Stage distribution across 14 more visits, spread over the last 10 days.
        $stages = ['waiting', 'vitals_taken', 'completed', 'billed', 'paid', 'paid', 'paid'];
        $created = 0;

        for ($i = 0; $i < 14; $i++) {
            $date = Carbon::today()->subDays($i % 10);
            $opdNo = $visitRepo->generateOpdNo($date->toDateString());

            if (OpdVisit::query()->where('opd_no', $opdNo)->exists()) {
                continue;
            }

            $patientId = $this->pickPatient($i + 10);
            $doctorId  = $this->pickDoctor($i + 2);
            $employee  = Employee::query()->find($doctorId);
            $deptId    = Department::query()->inRandomOrder()->value('id');
            $stage     = $stages[$i % count($stages)];

            $visit = DB::transaction(function () use ($opdNo, $patientId, $doctorId, $deptId, $date) {
                $v = OpdVisit::query()->forceCreate([
                    'opd_no' => $opdNo,
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'department_id' => $deptId,
                    'visit_type' => 'walk_in',
                    'visit_date' => $date->toDateString(),
                    'token_number' => rand(1, 30),
                    'status' => OpdVisitStatusEnum::WAITING,
                    'chief_complaint' => 'Routine check-up',
                    'created_by' => $this->receptionistId,
                    'updated_by' => $this->receptionistId,
                    'status_flag' => 1,
                    'sort_order' => 0,
                ]);

                app(OpdVisitRepository::class)->logAudit(
                    $v, OpdVisitActionEnum::CREATE, null, $v->status, $this->receptionistId,
                    'OPD visit created (demo)', ['patient_id' => $patientId],
                );

                return $v;
            });

            if (in_array($stage, ['vitals_taken', 'completed', 'billed', 'paid'], true)) {
                OpdVital::query()->forceCreate([
                    'opd_visit_id' => $visit->id, 'patient_id' => $patientId,
                    'recorded_by' => $this->receptionistId, 'recorded_at' => now(),
                    'bp_systolic' => rand(105, 140), 'bp_diastolic' => rand(65, 90), 'pulse_bpm' => rand(60, 100),
                    'temperature_c' => round(rand(365, 385) / 10, 1), 'spo2_pct' => rand(95, 99),
                    'weight_kg' => rand(45, 90), 'height_cm' => rand(150, 180),
                    'created_by' => $this->receptionistId, 'updated_by' => $this->receptionistId,
                    'status_flag' => 1, 'sort_order' => 0, 'status' => 1,
                ]);
                $visitRepo->transitionStatus($visit->id, OpdVisitStatusEnum::VITALS_TAKEN, $this->receptionistId, 'Vitals recorded (demo)', [], OpdVisitActionEnum::STATUS_CHANGE);

                OpdDiagnosis::query()->forceCreate([
                    'opd_visit_id' => $visit->id, 'patient_id' => $patientId,
                    'icd10_code' => 'Z00.0', 'diagnosis_name' => 'General medical examination',
                    'diagnosis_type' => 'primary', 'sequence' => 1, 'is_primary' => true, 'is_confirmed' => true,
                    'recorded_by' => $this->actorId,
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId,
                    'status_flag' => 1, 'sort_order' => 0, 'status' => 1,
                ]);
            }

            if (in_array($stage, ['completed', 'billed', 'paid'], true)) {
                $visitRepo->transitionStatus($visit->id, OpdVisitStatusEnum::IN_CONSULTATION, $this->actorId, 'Consultation started (demo)', [], OpdVisitActionEnum::STATUS_CHANGE);
                $visitRepo->transitionStatus($visit->id, OpdVisitStatusEnum::COMPLETED, $this->actorId, 'Consultation completed (demo)', [], OpdVisitActionEnum::STATUS_CHANGE);
            }

            if (in_array($stage, ['billed', 'paid'], true)) {
                $bill = $billService->generate($visit->id, $this->actorId, [
                    'auto_bill' => true,
                    'items' => [
                        ['item_type' => 'consultation', 'description' => 'Doctor consultation fee (demo)', 'quantity' => 1, 'unit_price' => 800],
                    ],
                ]);

                if ($stage === 'paid' && (float) $bill->total > 0) {
                    $billService->recordPayment($bill->id, [
                        'opd_bill_id' => $bill->id,
                        'payment_method' => ['cash', 'card', 'mobile'][array_rand(['cash', 'card', 'mobile'])],
                        'amount' => (float) $bill->total,
                        'paid_at' => now(),
                        'reference_no' => 'DEMO-PAY-' . $i,
                        'notes' => 'Demo payment',
                    ], $this->receptionistId);
                }
            }

            $created++;
        }

        $this->command->info("[AppointmentOpdDemoSeeder] OPD visits created: {$created}");
    }

    private function seedAllergies(): void
    {
        $allergens = [
            ['drug', 'Penicillin', 'Skin rash', 'moderate'],
            ['drug', 'Sulfa drugs', 'Hives', 'mild'],
            ['food', 'Peanuts', 'Anaphylaxis', 'severe'],
            ['food', 'Shellfish', 'Swelling', 'moderate'],
            ['environmental', 'Dust mites', 'Sneezing', 'mild'],
        ];

        foreach (array_slice($this->patientIds, 0, 10) as $i => $patientId) {
            $exists = DB::table('patient_allergies')->where('patient_id', $patientId)->exists();
            if ($exists) {
                continue;
            }
            [$type, $allergen, $reaction, $severity] = $allergens[$i % count($allergens)];

            PatientAllergy::query()->forceCreate([
                'patient_id' => $patientId,
                'allergy_type' => $type,
                'allergen_name' => $allergen,
                'reaction_type' => $reaction,
                'severity' => $severity,
                'recorded_by' => $this->actorId,
                'recorded_at' => now(),
                'status' => 1,
            ]);
        }
    }
}
