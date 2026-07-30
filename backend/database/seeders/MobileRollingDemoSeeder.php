<?php

namespace Database\Seeders;

use App\Models\ErVisit;
use App\Models\IpdAdmission;
use App\Models\IpdFluidBalance;
use App\Models\IpdMedicationAdministration;
use App\Models\IpdMedicationOrder;
use App\Models\IpdNursingAssessment;
use App\Models\IpdVital;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\Patient;
use App\Models\RadiologyOrder;
use App\Models\RadiologyOrderItem;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * ONE-COMMAND rolling demo dataset for the mobile apps.
 *
 *   php artisan db:seed --class=MobileRollingDemoSeeder
 *
 * Always centred on TODAY, spanning one week back → one week ahead, so every
 * feature across all six role apps shows live data relative to the current date.
 * Re-run any time: it force-deletes its own previous rows first (idempotent).
 *
 * It orchestrates the two existing rolling seeders, then layers a rolling week
 * of inpatient clinical activity on top:
 *   - AppointmentCurrentDemoSeeder  → appointments (today ±7)  [Patient/Doctor/Admin]
 *   - MobileFeaturesDemoSeeder      → nurse user + the 9 mobile net-new tables
 *   - this seeder                   → vitals trend, fluid balance, nursing notes,
 *                                     active lab orders (results inbox), active ED
 *                                     visits (ED board / emergency monitor) and
 *                                     MAR doses (given + due), all across ±7 days.
 *
 * Prerequisite base data (patients, wards, beds, IPD admissions, doctors) must
 * exist — run the base demo seeders once first (MasterDataDemoSeeder, OpdDemoSeeder,
 * IpdErDemoSeeder). NEVER run bare `php artisan db:seed` (AuthSeeder truncates users).
 */
class MobileRollingDemoSeeder extends Seeder
{
    /** Rows this seeder owns are tagged so re-runs can wipe & recreate them. */
    private const NOTE = '[ROLL]';
    private const LAB_PREFIX = 'ROLL-L';
    private const RAD_PREFIX = 'ROLL-R';
    private const ER_PREFIX = 'ROLL-ER';

    public function run(): void
    {
        // 1) Base rolling / mobile activity (both idempotent).
        $this->call([
            AppointmentCurrentDemoSeeder::class,
            MobileFeaturesDemoSeeder::class,
        ]);

        Model::unguard();
        try {
            $this->seedClinicalWindow();
        } finally {
            Model::reguard();
        }
    }

    private function seedClinicalWindow(): void
    {
        $today  = Carbon::today();
        $nurseId  = User::query()->where('email', 'nurse1@hms.local')->value('id') ?? 1;
        $doctorId = User::query()
            ->whereJsonContains('role_ids', (string) (Role::query()->where('name', 'Doctor')->value('id') ?: 9))
            ->value('id') ?? 1;

        $admissions = IpdAdmission::query()
            ->whereNull('discharge_date')->where('status', 1)
            ->orderBy('id')->limit(5)
            ->get(['id', 'patient_id', 'ward_id', 'bed_id']);

        if ($admissions->isEmpty()) {
            $this->command?->warn('MobileRollingDemoSeeder: no active admissions — run the IPD demo seeder first. Skipped clinical window.');
            return;
        }

        $this->clearPreviousOutput();

        $this->seedVitals($admissions, $today, $nurseId);
        $this->seedFluidBalance($admissions->take(2), $today, $nurseId);
        $this->seedNursingNotes($admissions->take(2), $today, $nurseId);
        $labCount = $this->seedLabOrders($admissions, $today, $doctorId);
        $radCount = $this->seedRadiologyOrders($admissions, $today, $doctorId);
        $erCount  = $this->seedErVisits($today, $nurseId);
        $marCount = $this->seedMar($admissions, $today, $nurseId);

        $this->command?->info(sprintf(
            'MobileRollingDemoSeeder: %s … %s | vitals=%d fluid=%d nursing=%d labOrders=%d radOrders=%d erVisits=%d marDoses=%d',
            $today->copy()->subDays(7)->toDateString(),
            $today->copy()->addDays(7)->toDateString(),
            IpdVital::where('notes', 'like', self::NOTE . '%')->count(),
            IpdFluidBalance::where('notes', 'like', self::NOTE . '%')->count(),
            IpdNursingAssessment::where('care_plan_notes', 'like', self::NOTE . '%')->count(),
            $labCount, $radCount, $erCount, $marCount,
        ));
    }

    private function clearPreviousOutput(): void
    {
        IpdVital::withTrashed()->where('notes', 'like', self::NOTE . '%')->forceDelete();
        IpdFluidBalance::withTrashed()->where('notes', 'like', self::NOTE . '%')->forceDelete();
        IpdMedicationAdministration::withTrashed()->where('notes', 'like', self::NOTE . '%')->forceDelete();
        // Nursing assessment is one-per-admission (unique) — handled via updateOrCreate, not delete.

        $labOrderIds = LabOrder::withTrashed()->where('lab_order_no', 'like', self::LAB_PREFIX . '%')->pluck('id');
        LabOrderItem::withTrashed()->whereIn('lab_order_id', $labOrderIds)->forceDelete();
        LabOrder::withTrashed()->where('lab_order_no', 'like', self::LAB_PREFIX . '%')->forceDelete();

        $radOrderIds = RadiologyOrder::withTrashed()->where('rad_order_no', 'like', self::RAD_PREFIX . '%')->pluck('id');
        RadiologyOrderItem::withTrashed()->whereIn('radiology_order_id', $radOrderIds)->forceDelete();
        RadiologyOrder::withTrashed()->where('rad_order_no', 'like', self::RAD_PREFIX . '%')->forceDelete();

        ErVisit::withTrashed()->where('er_visit_no', 'like', self::ER_PREFIX . '%')->forceDelete();
    }

    /** A week of daily obs per active admission (a real vitals trend). */
    private function seedVitals($admissions, Carbon $today, int $nurseId): void
    {
        foreach ($admissions as $adm) {
            for ($o = -7; $o <= 0; $o++) {
                $t = $today->copy()->addDays($o)->setTime(8, 0);
                IpdVital::create([
                    'admission_id'     => $adm->id,
                    'recorded_at'      => $t,
                    'recorded_by'      => $nurseId,
                    'bp_systolic'      => random_int(112, 142),
                    'bp_diastolic'     => random_int(66, 90),
                    'pulse_bpm'        => random_int(66, 102),
                    'temperature_c'    => round(36.4 + random_int(0, 16) / 10, 1),
                    'temperature_method' => 'oral',
                    'spo2_pct'         => random_int(93, 99),
                    'respiratory_rate' => random_int(14, 22),
                    'pain_score'       => random_int(0, 4),
                    'notes'            => self::NOTE . ' routine observation',
                    'status'           => 1,
                ]);
            }
        }
    }

    /** Intake + output entries per day for the past week. */
    private function seedFluidBalance($admissions, Carbon $today, int $nurseId): void
    {
        foreach ($admissions as $adm) {
            for ($o = -6; $o <= 0; $o++) {
                $d = $today->copy()->addDays($o);
                IpdFluidBalance::create([
                    'admission_id' => $adm->id, 'balance_type' => 'intake', 'category' => 'IV Fluid',
                    'amount_ml' => random_int(400, 900), 'shift' => 'morning',
                    'recorded_at' => $d->copy()->setTime(10, 0), 'recorded_by' => $nurseId,
                    'notes' => self::NOTE, 'status' => 1,
                ]);
                IpdFluidBalance::create([
                    'admission_id' => $adm->id, 'balance_type' => 'output', 'category' => 'Urine',
                    'amount_ml' => random_int(300, 750), 'shift' => 'evening',
                    'recorded_at' => $d->copy()->setTime(18, 0), 'recorded_by' => $nurseId,
                    'notes' => self::NOTE, 'status' => 1,
                ]);
            }
        }
    }

    /** Nursing assessment is one-per-admission (unique) — refresh the current one per re-run. */
    private function seedNursingNotes($admissions, Carbon $today, int $nurseId): void
    {
        foreach ($admissions as $adm) {
            IpdNursingAssessment::updateOrCreate(
                ['admission_id' => $adm->id],
                [
                    'general_appearance' => 'Alert and comfortable at rest',
                    'mobility_status'    => 'Independent with supervision',
                    'fall_risk_level'    => 'low',
                    'pain_assessment'    => 'Mild, 2/10 on movement',
                    'nutrition_risk'     => 'low',
                    'care_plan_notes'    => self::NOTE . ' Continue hourly rounding; encourage oral intake.',
                    'assessed_by'        => $nurseId,
                    'assessed_at'        => $today->copy()->setTime(7, 30),
                    'status'             => 1,
                ]
            );
        }
    }

    /** Active lab orders (not reported/cancelled) so the results inbox is populated. */
    private function seedLabOrders($admissions, Carbon $today, int $doctorId): int
    {
        $statuses = ['ordered', 'sample_collected', 'in_progress', 'verified'];
        $priorities = ['routine', 'urgent', 'stat'];
        $labTestIds = [1, 2, 3];
        $seq = 0;

        foreach ($admissions as $adm) {
            for ($o = -6; $o <= 0; $o += 2) {
                $seq++;
                $order = LabOrder::create([
                    'lab_order_no'        => self::LAB_PREFIX . str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                    'patient_id'          => $adm->patient_id,
                    'ipd_admission_id'    => $adm->id,
                    'order_source'        => 'ipd',
                    'ordered_by'          => $doctorId,
                    'ordered_at'          => $today->copy()->addDays($o)->setTime(9, 0),
                    'priority'            => $priorities[$seq % 3],
                    'clinical_indication' => 'Routine inpatient monitoring panel (demo)',
                    'order_status'        => $statuses[$seq % count($statuses)],
                    'status'              => 1,
                ]);
                foreach ($labTestIds as $i => $testId) {
                    LabOrderItem::create([
                        'lab_order_id'       => $order->id,
                        'lab_test_id'        => $testId,
                        'test_name_snapshot' => 'Lab test #' . $testId,
                        'item_status'        => 'ordered',
                        'sequence'           => $i + 1,
                        'status'             => 1,
                    ]);
                }
            }
        }

        return $seq;
    }

    /** Active radiology orders (not reported/cancelled) for the ward investigations tab. */
    private function seedRadiologyOrders($admissions, Carbon $today, int $doctorId): int
    {
        $statuses = ['ordered', 'in_progress'];
        $priorities = ['routine', 'urgent', 'stat'];
        $modalities = ['X-Ray', 'CT', 'Ultrasound'];
        $radTestIds = [1, 2];
        $seq = 0;

        foreach ($admissions as $adm) {
            for ($o = -5; $o <= 0; $o += 3) {
                $seq++;
                $order = RadiologyOrder::create([
                    'rad_order_no'        => self::RAD_PREFIX . str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                    'patient_id'          => $adm->patient_id,
                    'ipd_admission_id'    => $adm->id,
                    'order_source'        => 'ipd',
                    'ordered_by'          => $doctorId,
                    'ordered_at'          => $today->copy()->addDays($o)->setTime(11, 0),
                    'priority'            => $priorities[$seq % 3],
                    'clinical_indication' => 'Inpatient imaging review (demo)',
                    'order_status'        => $statuses[$seq % count($statuses)],
                    'status'              => 1,
                ]);
                foreach ($radTestIds as $i => $testId) {
                    RadiologyOrderItem::create([
                        'radiology_order_id' => $order->id,
                        'radiology_test_id'  => $testId,
                        'test_name_snapshot' => 'Imaging study #' . $testId,
                        'modality_snapshot'  => $modalities[$i % count($modalities)],
                        'item_status'        => 'ordered',
                        'sequence'           => $i + 1,
                        'status'             => 1,
                    ]);
                }
            }
        }

        return $seq;
    }

    /** Active ED visits arriving over the last few days (populates ED board + emergency monitor). */
    private function seedErVisits(Carbon $today, int $nurseId): int
    {
        $statuses = ['waiting_triage', 'triaged', 'in_treatment'];
        $complaints = ['Central chest pain', 'Shortness of breath', 'Fall — head injury', 'Abdominal pain', 'High fever', 'Palpitations'];
        $patientIds = Patient::query()->where('status', 1)->orderBy('id')->limit(8)->pluck('id')->all();
        if (empty($patientIds)) {
            return 0;
        }

        $count = 0;
        for ($o = -2; $o <= 0; $o++) {
            foreach ([0, 1] as $k) {          // two arrivals per day
                $idx = $count;
                ErVisit::create([
                    'er_visit_no'     => self::ER_PREFIX . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT),
                    'patient_id'      => $patientIds[$idx % count($patientIds)],
                    'arrival_mode'    => $k === 0 ? 'walk_in' : 'ambulance',
                    'chief_complaint' => $complaints[$idx % count($complaints)],
                    'arrival_at'      => $today->copy()->addDays($o)->setTime(9 + $idx, 15),
                    'er_status'       => $statuses[$idx % count($statuses)],
                    'disposition'     => null,
                    'registered_by'   => $nurseId,
                    'status'          => 1,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /** MAR doses: past = given, today/tomorrow = scheduled (so nurses see due meds). */
    private function seedMar($admissions, Carbon $today, int $nurseId): int
    {
        $orders = IpdMedicationOrder::query()
            ->whereIn('admission_id', $admissions->pluck('id'))
            ->get(['id']);

        $count = 0;
        foreach ($orders as $order) {
            for ($o = -2; $o <= 1; $o++) {
                foreach ([8, 20] as $hour) {
                    $scheduled = $today->copy()->addDays($o)->setTime($hour, 0);
                    $given = $scheduled->isPast();
                    IpdMedicationAdministration::create([
                        'order_id'              => $order->id,
                        'scheduled_at'          => $scheduled,
                        'administered_at'       => $given ? $scheduled->copy()->addMinutes(random_int(1, 20)) : null,
                        'administration_status' => $given ? 'given' : 'scheduled',
                        'administered_by'       => $given ? $nurseId : null,
                        'notes'                 => self::NOTE,
                        'status'                => 1,
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }
}
