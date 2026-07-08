<?php

namespace Database\Seeders;

use App\Models\AnaesthesiaRecord;
use App\Models\BloodCrossMatch;
use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodTransfusion;
use App\Models\BloodUnit;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpdAdmission;
use App\Models\OtBooking;
use App\Models\Patient;
use App\Models\Theatre;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Blood Bank + OT volume: ~10 donors -> donations -> screened units across
 * every unit_status (quarantine/available/discarded/issued) so the blood
 * inventory summary isn't all-quarantine, plus a handful of cross-matches
 * and transfusions, and ~8 OT bookings across the booking lifecycle with
 * anaesthesia records for the completed ones.
 *
 * Idempotent: donors keyed by `donor_no`, OT bookings by `booking_no`.
 */
class BloodBankOtDemoSeeder extends Seeder
{
    private int $actorId;

    public function run(): void
    {
        $this->command->info('[BloodBankOtDemoSeeder] Starting ...');
        $this->actorId = User::query()->where('email', 'doctor@hms.local')->value('id') ?? 1;

        $this->seedBloodBank();
        $this->seedOtBookings();

        $this->command->info('[BloodBankOtDemoSeeder] Done.');
    }

    private function seedBloodBank(): void
    {
        $names = ['Rafiqul Islam', 'Salma Khatun', 'Jubayer Ahmed', 'Taslima Akter', 'Monirul Haque',
            'Farida Yasmin', 'Shamim Reza', 'Nasima Begum', 'Kamrul Hasan', 'Ayesha Siddika'];
        $groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

        $unitIndex = 0;
        $unitStates = ['available', 'available', 'available', 'quarantine', 'discarded', 'issued', 'available', 'available'];

        foreach ($names as $i => $name) {
            $donorNo = 'DNR-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            $donor = BloodDonor::query()->firstOrCreate(
                ['donor_no' => $donorNo],
                [
                    'name' => $name, 'gender' => $i % 2 === 0 ? 'male' : 'female',
                    'dob' => Carbon::now()->subYears(rand(20, 50))->toDateString(),
                    'blood_group' => $groups[$i % count($groups)],
                    'phone' => '+8801' . rand(600000000, 999999999),
                    'total_donations' => 1, 'is_deferred' => false,
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ],
            );

            if (!$donor->wasRecentlyCreated) {
                continue;
            }

            $donationDate = Carbon::now()->subDays(rand(5, 60));
            $donation = BloodDonation::query()->create([
                'donation_no' => 'DON-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'donor_id' => $donor->id, 'donation_date' => $donationDate->toDateString(),
                'volume_ml' => 450, 'hemoglobin_g_dl' => round(rand(125, 160) / 10, 1),
                'collected_by' => $this->actorId,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            $unitStatus = $unitStates[$unitIndex % count($unitStates)];
            $unitIndex++;
            $screeningStatus = $unitStatus === 'discarded' ? 'failed' : ($unitStatus === 'quarantine' ? 'pending' : 'passed');

            BloodUnit::query()->create([
                'bag_no' => 'BAG-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'donation_id' => $donation->id, 'component_type' => 'whole_blood',
                'blood_group' => $donor->blood_group,
                'collection_date' => $donationDate->toDateString(),
                'expiry_date' => $donationDate->copy()->addDays(35)->toDateString(),
                'screening_status' => $screeningStatus, 'unit_status' => $unitStatus,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);
        }

        $this->command->info('[BloodBankOtDemoSeeder] Blood donors now: ' . BloodDonor::query()->count() . ', units now: ' . BloodUnit::query()->count());

        // Cross-match + transfusion for one 'issued' unit against a compatible patient.
        $issuedUnit = BloodUnit::query()->where('unit_status', 'issued')->first();
        $patient = Patient::query()->where('blood_group', $issuedUnit?->blood_group)->first()
            ?? Patient::query()->first();

        if ($issuedUnit && $patient && !BloodCrossMatch::query()->where('blood_unit_id', $issuedUnit->id)->exists()) {
            $crossMatch = BloodCrossMatch::query()->create([
                'patient_id' => $patient->id, 'blood_unit_id' => $issuedUnit->id,
                'patient_blood_group' => $patient->blood_group ?? $issuedUnit->blood_group,
                'cross_match_result' => 'compatible', 'method' => 'immediate_spin',
                'performed_by' => $this->actorId, 'performed_at' => now(),
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            BloodTransfusion::query()->create([
                'patient_id' => $patient->id, 'blood_unit_id' => $issuedUnit->id,
                'cross_match_id' => $crossMatch->id,
                'started_at' => now()->subHours(2), 'ended_at' => now()->subHours(1),
                'reaction_observed' => false, 'administered_by' => $this->actorId,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);
        }
    }

    private function seedOtBookings(): void
    {
        $theatreIds = Theatre::query()->pluck('id')->all();
        $surgeonIds = Employee::query()->where('employee_type', 'doctor')->pluck('id')->all();
        $patientIds = Patient::query()->pluck('id')->all();
        $admissionIds = IpdAdmission::query()->pluck('id')->all();

        if (empty($theatreIds) || empty($surgeonIds) || empty($patientIds)) {
            return;
        }

        $surgeries = ['Appendectomy', 'Cholecystectomy', 'Cesarean Section', 'Hernia Repair', 'Cataract Surgery',
            'Tonsillectomy', 'Fracture Fixation', 'Coronary Angiography'];
        $statuses = ['scheduled', 'scheduled', 'in_progress', 'completed', 'completed', 'completed', 'cancelled', 'completed'];

        $created = 0;
        foreach ($surgeries as $i => $surgery) {
            $bookingNo = 'OTB-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            if (DB::table('ot_bookings')->where('booking_no', $bookingNo)->exists()) {
                continue;
            }

            $status = $statuses[$i % count($statuses)];
            $dayOffset = in_array($status, ['completed', 'cancelled'], true) ? -rand(1, 15) : rand(0, 10);
            $date = Carbon::today()->addDays($dayOffset);

            $booking = OtBooking::query()->forceCreate([
                'booking_no' => $bookingNo,
                'patient_id' => $patientIds[$i % count($patientIds)],
                'ipd_admission_id' => !empty($admissionIds) ? $admissionIds[$i % count($admissionIds)] : null,
                'theatre_id' => $theatreIds[$i % count($theatreIds)],
                'department_id' => Department::query()->inRandomOrder()->value('id'),
                'surgeon_id' => $surgeonIds[$i % count($surgeonIds)],
                'anaesthetist_id' => $surgeonIds[($i + 1) % count($surgeonIds)],
                'surgery_name' => $surgery, 'surgery_type' => 'elective',
                'scheduled_date' => $date->toDateString(),
                'scheduled_start_time' => '09:00:00', 'scheduled_end_time' => '11:00:00',
                'actual_start_time' => $status === 'completed' ? $date->copy()->setTime(9, 5) : null,
                'actual_end_time' => $status === 'completed' ? $date->copy()->setTime(10, 45) : null,
                'booking_status' => $status,
                'cancellation_reason' => $status === 'cancelled' ? 'Patient not fit for surgery (demo)' : null,
                'booked_by' => $this->actorId,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            if ($status === 'completed') {
                AnaesthesiaRecord::query()->forceCreate([
                    'ot_booking_id' => $booking->id, 'anaesthetist_id' => $booking->anaesthetist_id,
                    'anaesthesia_type' => 'general', 'asa_grade' => 'II',
                    'premedication' => 'Midazolam 2mg IV',
                    'induction_agent' => 'Propofol', 'airway_management' => 'Endotracheal tube',
                    'notes' => 'Uneventful anaesthesia course (demo)',
                    'started_at' => $date->copy()->setTime(8, 55), 'ended_at' => $date->copy()->setTime(10, 50),
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ]);
            }

            $created++;
        }

        $this->command->info("[BloodBankOtDemoSeeder] OT bookings created: {$created}");
    }
}
