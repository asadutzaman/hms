<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\Department;
use App\Models\ErTriage;
use App\Models\ErVisit;
use App\Models\Employee;
use App\Models\IpdAdmission;
use App\Models\IpdBill;
use App\Models\IpdBillItem;
use App\Models\IpdMedicationAdministration;
use App\Models\IpdMedicationOrder;
use App\Models\IpdNursingAssessment;
use App\Models\IpdVital;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * IPD admission volume (bed occupancy, vitals trend, medication administration,
 * nursing assessment, billing) and ER volume (triage-level distribution) so
 * the bed-occupancy dashboard, MAR worklist, and ER triage board all have
 * something to render. Bypasses the admit/discharge service layer (forceCreate
 * direct on the models) since this is demo volume, not a business-rule test —
 * but still flips `beds.bed_status` manually so occupancy counts stay correct,
 * matching the invariant `IpdAdmissionRepository::admit()` normally enforces.
 *
 * Idempotent: admissions keyed by `admission_no`, ER visits by `er_visit_no`.
 */
class IpdErDemoSeeder extends Seeder
{
    private array $patientIds;
    private array $doctorEmployeeIds;
    private int $nurseUserId;
    private int $actorId;

    public function run(): void
    {
        $this->command->info('[IpdErDemoSeeder] Starting ...');

        $this->patientIds = Patient::query()->pluck('id')->all();
        $this->doctorEmployeeIds = Employee::query()->where('employee_type', 'doctor')->pluck('id')->all();
        $this->nurseUserId = User::query()->where('email', 'reception@hms.local')->value('id') ?? 1;
        $this->actorId = User::query()->where('email', 'doctor@hms.local')->value('id') ?? 1;

        $this->seedIpdAdmissions();
        $this->seedErVisits();

        $this->command->info('[IpdErDemoSeeder] Done.');
    }

    private function seedIpdAdmissions(): void
    {
        $created = 0;

        // 5 active admissions (currently occupying a bed) + 4 discharged.
        $plan = [
            ['status' => 'admitted', 'daysAgo' => 3],
            ['status' => 'admitted', 'daysAgo' => 1],
            ['status' => 'admitted', 'daysAgo' => 5],
            ['status' => 'admitted', 'daysAgo' => 2],
            ['status' => 'admitted', 'daysAgo' => 0],
            ['status' => 'discharged', 'daysAgo' => 10],
            ['status' => 'discharged', 'daysAgo' => 15],
            ['status' => 'discharged', 'daysAgo' => 8],
            ['status' => 'dama', 'daysAgo' => 12],
        ];

        foreach ($plan as $i => $row) {
            $admissionNo = 'IPD-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            if (IpdAdmission::query()->where('admission_no', $admissionNo)->exists()) {
                continue;
            }

            $bed = Bed::query()->where('bed_status', 'vacant')->inRandomOrder()->first();
            if (!$bed) {
                $this->command->warn('[IpdErDemoSeeder] No vacant beds left; stopping admission seeding.');
                break;
            }

            $patientId = $this->patientIds[$i % count($this->patientIds)];
            $doctorId  = $this->doctorEmployeeIds[$i % count($this->doctorEmployeeIds)];
            $admissionDate = Carbon::now()->subDays($row['daysAgo']);
            $isActive = $row['status'] === 'admitted';

            $admission = IpdAdmission::query()->forceCreate([
                'admission_no' => $admissionNo,
                'patient_id' => $patientId,
                'admission_type' => $i % 3 === 0 ? 'emergency' : 'planned',
                'attending_doctor_id' => $doctorId,
                'department_id' => Department::query()->inRandomOrder()->value('id'),
                'ward_id' => $bed->ward_id,
                'bed_id' => $bed->id,
                'admission_date' => $admissionDate,
                'expected_discharge_date' => $admissionDate->copy()->addDays(5)->toDateString(),
                'discharge_date' => $isActive ? null : $admissionDate->copy()->addDays(rand(2, 6)),
                'admission_status' => $row['status'],
                'diagnosis_at_admission' => 'Acute condition requiring in-patient management (demo)',
                'admitted_by' => $this->actorId,
                'discharged_by' => $isActive ? null : $this->actorId,
                'created_by' => $this->actorId,
                'updated_by' => $this->actorId,
                'status' => 1,
                'sort_order' => 0,
            ]);

            $bed->bed_status = $isActive ? 'occupied' : 'vacant';
            $bed->save();

            // Vitals trend: 3 readings.
            for ($v = 0; $v < 3; $v++) {
                IpdVital::query()->forceCreate([
                    'admission_id' => $admission->id,
                    'recorded_at' => $admissionDate->copy()->addHours($v * 8),
                    'recorded_by' => $this->nurseUserId,
                    'bp_systolic' => rand(105, 135), 'bp_diastolic' => rand(65, 88),
                    'pulse_bpm' => rand(65, 100), 'temperature_c' => round(rand(365, 380) / 10, 1),
                    'spo2_pct' => rand(94, 99), 'respiratory_rate' => rand(14, 20),
                    'weight_kg' => rand(45, 90), 'height_cm' => rand(150, 180),
                    'created_by' => $this->nurseUserId, 'updated_by' => $this->nurseUserId, 'status' => 1,
                ]);
            }

            // Nursing assessment (unique per admission).
            IpdNursingAssessment::query()->forceCreate([
                'admission_id' => $admission->id,
                'general_appearance' => 'Alert and oriented, stable condition',
                'mobility_status' => 'assisted',
                'fall_risk_score' => rand(0, 25),
                'fall_risk_level' => 'low',
                'assessed_by' => $this->nurseUserId,
                'assessed_at' => $admissionDate,
                'created_by' => $this->nurseUserId, 'updated_by' => $this->nurseUserId, 'status' => 1,
            ]);

            // Medication order + a couple of administrations.
            $order = IpdMedicationOrder::query()->forceCreate([
                'admission_id' => $admission->id,
                'drug_name' => 'Ceftriaxone 1g',
                'route' => 'iv', 'frequency' => 'BD',
                'duration_value' => 5, 'duration_unit' => 'days',
                'start_date' => $admissionDate->toDateString(),
                'order_status' => $isActive ? 'active' : 'completed',
                'ordered_by' => $this->actorId, 'ordered_at' => $admissionDate,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            for ($a = 0; $a < 2; $a++) {
                IpdMedicationAdministration::query()->forceCreate([
                    'order_id' => $order->id,
                    'scheduled_at' => $admissionDate->copy()->addHours($a * 12),
                    'administered_at' => $admissionDate->copy()->addHours($a * 12)->addMinutes(10),
                    'administration_status' => 'given',
                    'administered_by' => $this->nurseUserId,
                    'created_by' => $this->nurseUserId, 'updated_by' => $this->nurseUserId, 'status' => 1,
                ]);
            }

            // Bill (+ a room-charge and pharmacy item), paid for discharged admissions.
            $days = max(1, $admissionDate->diffInDays($isActive ? now() : $admission->discharge_date));
            $roomTotal = $days * 1500;
            $pharmacyTotal = 900;
            $subtotal = $roomTotal + $pharmacyTotal;

            $bill = IpdBill::query()->forceCreate([
                'admission_id' => $admission->id,
                'bill_no' => 'IPB-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal, 'discount' => 0, 'tax' => 0, 'total' => $subtotal,
                'paid' => $isActive ? 0 : $subtotal,
                'balance' => $isActive ? $subtotal : 0,
                'bill_status' => $isActive ? 'unpaid' : 'paid',
                'is_finalized' => !$isActive,
                'billed_by' => $this->actorId, 'billed_at' => $admissionDate,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            IpdBillItem::query()->forceCreate([
                'ipd_bill_id' => $bill->id, 'item_type' => 'room_charge',
                'description' => "Room charge ({$days} days)", 'quantity' => $days,
                'unit_price' => 1500, 'line_total' => $roomTotal, 'sequence' => 1,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);
            IpdBillItem::query()->forceCreate([
                'ipd_bill_id' => $bill->id, 'item_type' => 'pharmacy',
                'description' => 'Medications administered', 'quantity' => 1,
                'unit_price' => $pharmacyTotal, 'line_total' => $pharmacyTotal, 'sequence' => 2,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            $created++;
        }

        $this->command->info("[IpdErDemoSeeder] IPD admissions created: {$created}");
    }

    private function seedErVisits(): void
    {
        $created = 0;
        $dispositions = ['admitted', 'discharged', 'discharged', 'referred'];
        $levels = [1, 2, 2, 3, 3, 3, 4, 5];
        $colorBands = [1 => 'red', 2 => 'orange', 3 => 'yellow', 4 => 'green', 5 => 'blue'];

        for ($i = 1; $i <= 8; $i++) {
            $visitNo = 'ER-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            if (DB::table('er_visits')->where('er_visit_no', $visitNo)->exists()) {
                continue;
            }

            $arrival = Carbon::now()->subHours(rand(1, 240));
            $level = $levels[$i % count($levels)];
            $status = $i <= 5 ? 'discharged' : 'in_treatment';

            $erVisit = ErVisit::query()->forceCreate([
                'er_visit_no' => $visitNo,
                'patient_id' => $this->patientIds[$i % count($this->patientIds)],
                'arrival_mode' => $i % 2 === 0 ? 'ambulance' : 'walk_in',
                'chief_complaint' => 'Acute chest pain and shortness of breath (demo)',
                'arrival_at' => $arrival,
                'er_status' => $status,
                'disposition' => $status === 'discharged' ? $dispositions[$i % count($dispositions)] : null,
                'disposed_at' => $status === 'discharged' ? $arrival->copy()->addHours(3) : null,
                'registered_by' => $this->nurseUserId,
                'created_by' => $this->nurseUserId, 'updated_by' => $this->nurseUserId, 'status' => 1,
            ]);

            ErTriage::query()->forceCreate([
                'er_visit_id' => $erVisit->id,
                'triage_level' => $level,
                'color_band' => $colorBands[$level],
                'target_minutes' => $level * 15,
                'bp_systolic' => rand(100, 150), 'bp_diastolic' => rand(60, 95),
                'pulse_bpm' => rand(70, 120), 'temperature_c' => round(rand(365, 390) / 10, 1),
                'spo2_pct' => rand(90, 99), 'respiratory_rate' => rand(14, 26),
                'triaged_by' => $this->nurseUserId, 'triaged_at' => $arrival->copy()->addMinutes(5),
                'created_by' => $this->nurseUserId, 'updated_by' => $this->nurseUserId, 'status' => 1,
            ]);

            $created++;
        }

        $this->command->info("[IpdErDemoSeeder] ER visits created: {$created}");
    }
}
