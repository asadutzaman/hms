<?php

namespace Database\Seeders;

use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LabTest;
use App\Models\OpdDiagnosis;
use App\Models\OpdInvestigationOrder;
use App\Models\OpdInvestigationOrderItem;
use App\Models\OpdPrescription;
use App\Models\OpdPrescriptionItem;
use App\Models\OpdVisit;
use App\Models\OpdVital;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\OpdBillRepository;
use App\Repositories\OpdVisitRepository;
use App\Services\Opd\OpdBillService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data seeder for the OPD module.
 *
 * Creates a complete, realistic OPD visit lifecycle so that the feature tests
 * (Phase K), the frontend (Phase I/J), and manual smoke testing have data to
 * work against.
 *
 * Lifecycle seeded:
 *   1. Foundation  - 1 department, 1 doctor (User + Employee), 1 receptionist,
 *                    2 patients.
 *   2. Lab catalogue touch-up - ensures 3 demo lab tests exist (idempotent).
 *   3. Walk-in OPD visit for patient #1 -> status = waiting.
 *   4. Vitals captured -> auto-transitions waiting -> vitals_taken.
 *   5. Diagnosis: 1 primary + 1 secondary.
 *   6. Prescription: 1 header + 2 drug items with snapshot prices.
 *   7. Investigation order: 1 header + 2 lab-test items (price snapshot).
 *   8. Consultation completed -> transition vitals_taken -> in_consultation ->
 *      completed (manual, since the validator bypass is needed).
 *   9. Bill generated via OpdBillRepository::generateFromVisit (auto-transitions
 *      completed -> billed when auto_bill=true and no manual discount).
 *  10. Full cash payment recorded via OpdBillRepository::recordPayment
 *      (status unpaid -> paid, balance cleared).
 *  11. Audit logs are emitted by the repos for each step. We additionally emit
 *      logs for vitals / diagnosis / prescription / order that the repos would
 *      normally emit (OpdDiagnosisRepository, OpdVitalRepository, etc.) -- but
 *      since we bypass those validators we write the audit entries directly via
 *      OpdVisitRepository::logAudit().
 *
 * Idempotency:
 *   - Shared entities (department, users, patients, lab tests) are find-or-create
 *     keyed by unique natural keys (name / email / mrn / code).
 *   - The OPD visit is keyed by `opd_no` for today; if it already exists we
 *     short-circuit and only report the existing visit.
 *
 * Notes on field mismatches:
 *   The OPD models and validators were written before the migrations and have a
 *   number of $fillable / column discrepancies (vitals use bp_systolic /
 *   temperature_c but the migration has systolic / temperature; diagnosis
 *   model has diagnosis_name / is_primary but migration has description; etc.).
 *   Repositories that have been migrated (OpdVisitRepository, OpdBillRepository)
 *   already use Model::forceCreate() to bypass $fillable; this seeder follows
 *   the same pattern for the remaining tables.
 */
class OpdDemoSeeder extends Seeder
{
    private int $actorId;          // doctor user id - default actor
    private int $receptionistId;   // receptionist user id
    private int $doctorEmployeeId; // employees.id of the doctor
    private int $departmentId;
    private int $patientOneId;
    private int $patientTwoId;
    private int $cbcLabTestId;
    private int $lipidLabTestId;

    public function run(): void
    {
        $this->command->info('[OpdDemoSeeder] Starting OPD demo seed ...');

        // -------------------------------------------------------------------------
        // 1. Foundation entities
        // -------------------------------------------------------------------------
        $this->seedFoundation();

        // -------------------------------------------------------------------------
        // 2. Idempotency guard - if today's demo visit already exists, stop.
        // -------------------------------------------------------------------------
        $today     = Carbon::today()->toDateString();
        $opdNo     = app(OpdVisitRepository::class)->generateOpdNo($today);
        $existing  = OpdVisit::query()->where('opd_no', $opdNo)->first();

        if ($existing) {
            $this->command->warn(
                "[OpdDemoSeeder] Today's demo visit {$opdNo} already exists (id={$existing->id}); skipping re-seed."
            );
            $this->printSummary($existing);
            return;
        }

        // -------------------------------------------------------------------------
        // 3. Walk-in OPD visit -> status = waiting
        // -------------------------------------------------------------------------
        $visit = $this->createWalkInVisit($opdNo);
        $this->command->info("[OpdDemoSeeder] Created OPD visit {$visit->opd_no} (id={$visit->id}, status={$visit->status})");

        // -------------------------------------------------------------------------
        // 4. Capture vitals -> auto-transition waiting -> vitals_taken
        // -------------------------------------------------------------------------
        $this->captureVitals($visit);
        $visit->refresh();
        $this->command->info("[OpdDemoSeeder] Recorded vitals, visit status now {$visit->status}");

        // -------------------------------------------------------------------------
        // 5. Diagnoses (primary + secondary)
        // -------------------------------------------------------------------------
        $this->createDiagnoses($visit);
        $this->command->info('[OpdDemoSeeder] Recorded diagnoses (primary + secondary)');

        // -------------------------------------------------------------------------
        // 6. Prescription (header + 2 items)
        // -------------------------------------------------------------------------
        $this->createPrescription($visit);
        $this->command->info('[OpdDemoSeeder] Recorded prescription with 2 items');

        // -------------------------------------------------------------------------
        // 7. Investigation order (header + 2 lab items with snapshot prices)
        // -------------------------------------------------------------------------
        $this->createInvestigationOrder($visit);
        $this->command->info('[OpdDemoSeeder] Recorded investigation order with 2 items');

        // -------------------------------------------------------------------------
        // 8. Drive visit forward: vitals_taken -> in_consultation -> completed
        // -------------------------------------------------------------------------
        $this->advanceVisit($visit);
        $visit->refresh();
        $this->command->info("[OpdDemoSeeder] Visit advanced to {$visit->status}");

        // -------------------------------------------------------------------------
        // 9. Generate the bill. The repo is idempotent and will auto-transition
        //    completed -> billed when auto_bill=true.
        // -------------------------------------------------------------------------
        $bill = app(OpdBillService::class)->generate($visit->id, $this->actorId, ['auto_bill' => true]);
        $visit->refresh();
        $this->command->info(sprintf(
            '[OpdDemoSeeder] Bill %s generated, total=%.2f, visit status=%s',
            $bill->bill_no,
            (float) $bill->total,
            $visit->status,
        ));

        // -------------------------------------------------------------------------
        // 10. Record a full cash payment. The repo will recompute paid / balance
        //     and set status = paid.
        // -------------------------------------------------------------------------
        $bill = app(OpdBillService::class)->recordPayment(
            $bill->id,
            [
                'opd_bill_id'    => $bill->id,
                'payment_method' => 'cash',
                'amount'         => (float) $bill->total,
                'paid_at'        => now(),
                'reference_no'   => 'DEMO-CASH-001',
                'notes'          => 'Full payment at counter (demo seed)',
            ],
            $this->receptionistId,
        );
        $visit->refresh();
        $this->command->info(sprintf(
            '[OpdDemoSeeder] Payment recorded: bill %s status=%s, visit status=%s',
            $bill->bill_no,
            $bill->status,
            $visit->status,
        ));

        $this->printSummary($visit);
        $this->command->info('[OpdDemoSeeder] Done.');
    }

    /* ---------------------------------------------------------------------- */
    /* Foundation                                                            */
    /* ---------------------------------------------------------------------- */

    private function seedFoundation(): void
    {
        // --- Department -----------------------------------------------------
        $department = Department::query()->updateOrCreate(
            ['name' => 'General OPD'],
            [
                'description' => 'General Out-Patient Department (demo)',
                'status'      => 1,
                'updated_by'  => null,
            ],
        );
        $this->departmentId = $department->id;

        // --- Doctor user ----------------------------------------------------
        $doctor = User::query()->updateOrCreate(
            ['email' => 'doctor@hms.local'],
            [
                'first_name'  => 'Demo',
                'last_name'   => 'Doctor',
                'user_type'   => 'employee',
                'phone'       => '+8801700000001',
                'password'    => Hash::make('password'),
                'web_access'  => 1,
                'app_access'  => 1,
                'is_verified' => 1,
                'status'      => 1,
            ],
        );
        $this->actorId = $doctor->id;

        // --- Doctor employee record -----------------------------------------
        $employee = Employee::query()->updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'employee_id'   => 'EMP-DOC-001',
                'name_en'       => 'Dr. Demo Doctor',
                'gender'        => 'male',
                'mobile'        => '+8801700000001',
                'employee_type' => 'doctor',
                'status'        => 1,
            ],
        );
        $this->doctorEmployeeId = $employee->id;

        // --- Receptionist user ----------------------------------------------
        $reception = User::query()->updateOrCreate(
            ['email' => 'reception@hms.local'],
            [
                'first_name'  => 'Demo',
                'last_name'   => 'Receptionist',
                'user_type'   => 'employee',
                'phone'       => '+8801700000002',
                'password'    => Hash::make('password'),
                'web_access'  => 1,
                'app_access'  => 1,
                'is_verified' => 1,
                'status'      => 1,
            ],
        );
        $this->receptionistId = $reception->id;

        // --- Patients -------------------------------------------------------
        $patient1 = Patient::query()->updateOrCreate(
            ['mrn' => 'MRN-DEMO-0001'],
            [
                'first_name'        => 'Rahim',
                'last_name'         => 'Uddin',
                'date_of_birth'     => '1985-04-12',
                'gender'            => 'male',
                'blood_group'       => 'B+',
                'primary_phone'     => '+8801711111111',
                'current_address'   => 'House 12, Road 7, Dhanmondi, Dhaka',
                'current_city'      => 'Dhaka',
                'current_country'   => 'Bangladesh',
                'registration_date' => Carbon::today()->toDateString(),
                'status'            => 1,
            ],
        );
        $this->patientOneId = $patient1->id;

        $patient2 = Patient::query()->updateOrCreate(
            ['mrn' => 'MRN-DEMO-0002'],
            [
                'first_name'        => 'Karima',
                'last_name'         => 'Begum',
                'date_of_birth'     => '1992-09-23',
                'gender'            => 'female',
                'blood_group'       => 'O+',
                'primary_phone'     => '+8801722222222',
                'current_address'   => 'House 4, Road 3, Uttara, Dhaka',
                'current_city'      => 'Dhaka',
                'current_country'   => 'Bangladesh',
                'registration_date' => Carbon::today()->toDateString(),
                'status'            => 1,
            ],
        );
        $this->patientTwoId = $patient2->id;

        // --- Lab tests (ensure demo tests exist regardless of LabTestSeeder) -
        $cbc = LabTest::query()->updateOrCreate(
            ['code' => 'CBC'],
            [
                'name'          => 'Complete Blood Count (CBC)',
                'category'      => 'haematology',
                'sample_type'   => 'EDTA Blood',
                'tat_hours'     => 4,
                'default_price' => 350.00,
                'is_active'     => true,
                'status'        => 1,
            ],
        );
        $this->cbcLabTestId = $cbc->id;

        $lipid = LabTest::query()->updateOrCreate(
            ['code' => 'LIPID'],
            [
                'name'          => 'Lipid Profile',
                'category'      => 'biochemistry',
                'sample_type'   => 'Serum',
                'tat_hours'     => 8,
                'default_price' => 900.00,
                'is_active'     => true,
                'status'        => 1,
            ],
        );
        $this->lipidLabTestId = $lipid->id;
    }

    /* ---------------------------------------------------------------------- */
    /* Visit                                                                  */
    /* ---------------------------------------------------------------------- */

    private function createWalkInVisit(string $opdNo): OpdVisit
    {
        // Bypass model $fillable (uses 'opd_no' as the natural key) and the
        // service layer's validator (which would require appointment_id etc
        // depending on visit_type). For a walk-in, no appointment linkage.
        return DB::transaction(function () use ($opdNo) {
            $visit = OpdVisit::query()->forceCreate([
                'opd_no'         => $opdNo,
                'patient_id'     => $this->patientOneId,
                'doctor_id'      => $this->doctorEmployeeId,
                'department_id'  => $this->departmentId,
                'visit_type'     => 'walk_in',
                'visit_date'     => Carbon::today()->toDateString(),
                'token_number'   => 1,
                'status'         => OpdVisitStatusEnum::WAITING,
                'chief_complaint'=> 'Fever and dry cough for 4 days; mild fatigue.',
                'history'        => 'No known drug allergies. No chronic illness. No recent travel.',
                'created_by'     => $this->receptionistId,
                'updated_by'     => $this->receptionistId,
                'status_flag'    => 1,
                'sort_order'     => 0,
            ]);

            app(OpdVisitRepository::class)->logAudit(
                $visit,
                OpdVisitActionEnum::CREATE,
                null,
                $visit->status,
                $this->receptionistId,
                'OPD visit created (walk-in)',
                [
                    'visit_type'      => $visit->visit_type,
                    'department_id'   => $visit->department_id,
                    'patient_id'      => $visit->patient_id,
                ],
            );

            return $visit;
        });
    }

    /* ---------------------------------------------------------------------- */
    /* Vitals                                                                 */
    /* ---------------------------------------------------------------------- */

    private function captureVitals(OpdVisit $visit): void
    {
        // Migration columns: systolic, diastolic, pulse, temperature, spo2,
        // weight, height, bmi. Model $fillable uses different names so we
        // forceCreate() directly.
        $vital = OpdVital::query()->forceCreate([
            'opd_visit_id'  => $visit->id,
            'patient_id'    => $visit->patient_id,
            'recorded_by'   => $this->receptionistId,
            'recorded_at'   => now(),
            'systolic'      => 128,
            'diastolic'     => 82,
            'pulse'         => 92,
            'temperature'   => 38.4,
            'spo2'          => 97,
            'weight'        => 68.5,
            'height'        => 168.0,
            'bmi'           => round(68.5 / (1.68 * 1.68), 2),
            'notes'         => 'Mild fever; otherwise stable.',
            'created_by'    => $this->receptionistId,
            'updated_by'    => $this->receptionistId,
            'status_flag'   => 1,
            'sort_order'    => 0,
            'status'        => 1,
        ]);

        app(OpdVisitRepository::class)->logAudit(
            $visit,
            OpdVisitActionEnum::VITALS_SAVED,
            null,
            null,
            $this->receptionistId,
            'Vitals captured',
            ['opd_vital_id' => $vital->id],
        );

        // Use the repo's atomic transition for waiting -> vitals_taken so the
        // audit log is written in the same transaction by the repo itself.
        app(OpdVisitRepository::class)->transitionStatus(
            $visit->id,
            OpdVisitStatusEnum::VITALS_TAKEN,
            $this->receptionistId,
            'Vitals recorded',
            ['vital_id' => $vital->id],
            OpdVisitActionEnum::STATUS_CHANGE,
        );
    }

    /* ---------------------------------------------------------------------- */
    /* Diagnoses                                                              */
    /* ---------------------------------------------------------------------- */

    private function createDiagnoses(OpdVisit $visit): void
    {
        // Migration columns for opd_diagnoses: icd10_code, description,
        // diagnosis_type, sequence. (Model $fillable adds extras that don't
        // exist on the table; forceCreate skips them.)
        OpdDiagnosis::query()->forceCreate([
            'opd_visit_id'  => $visit->id,
            'patient_id'    => $visit->patient_id,
            'icd10_code'    => 'J06.9',
            'description'   => 'Acute upper respiratory infection, unspecified',
            'diagnosis_type'=> 'primary',
            'sequence'      => 1,
            'created_by'    => $this->actorId,
            'updated_by'    => $this->actorId,
            'status_flag'   => 1,
            'sort_order'    => 0,
            'status'        => 1,
        ]);

        OpdDiagnosis::query()->forceCreate([
            'opd_visit_id'  => $visit->id,
            'patient_id'    => $visit->patient_id,
            'icd10_code'    => 'R50.9',
            'description'   => 'Fever, unspecified',
            'diagnosis_type'=> 'secondary',
            'sequence'      => 2,
            'created_by'    => $this->actorId,
            'updated_by'    => $this->actorId,
            'status_flag'   => 1,
            'sort_order'    => 1,
            'status'        => 1,
        ]);

        app(OpdVisitRepository::class)->logAudit(
            $visit,
            OpdVisitActionEnum::DIAGNOSIS_SAVED,
            null,
            null,
            $this->actorId,
            'Diagnoses recorded',
            [
                'count'      => 2,
                'primary'    => 'J06.9',
                'secondary'  => 'R50.9',
            ],
        );
    }

    /* ---------------------------------------------------------------------- */
    /* Prescription                                                           */
    /* ---------------------------------------------------------------------- */

    private function createPrescription(OpdVisit $visit): void
    {
        // Migration columns for opd_prescriptions: prescribed_by, prescribed_at,
        // notes, advice. For opd_prescription_items: drug_name, dose, frequency,
        // duration_days, route, instructions, sequence.
        $rx = OpdPrescription::query()->forceCreate([
            'opd_visit_id'   => $visit->id,
            'patient_id'     => $visit->patient_id,
            'prescribed_by'  => $this->actorId,
            'prescribed_at'  => now(),
            'advice'         => 'Drink plenty of fluids. Rest. Return if fever > 39C or breathing difficulty.',
            'notes'          => null,
            'created_by'     => $this->actorId,
            'updated_by'     => $this->actorId,
            'status_flag'    => 1,
            'sort_order'     => 0,
            'status'         => 1,
        ]);

        // Drug items - we set opd_visit_id on the item too (the model $fillable
        // includes it; the migration doesn't have the column on the items
        // table so we DON'T pass it here). We pass only the migration columns.
        OpdPrescriptionItem::query()->forceCreate([
            'opd_prescription_id' => $rx->id,
            'drug_name'           => 'Paracetamol 500mg',
            'dose'                => '1 tab',
            'frequency'           => 'TID',
            'duration_days'       => 5,
            'route'               => 'oral',
            'instructions'        => 'After meals',
            'sequence'            => 1,
            'created_by'          => $this->actorId,
            'updated_by'          => $this->actorId,
            'status_flag'         => 1,
            'sort_order'          => 0,
            'status'              => 1,
        ]);

        OpdPrescriptionItem::query()->forceCreate([
            'opd_prescription_id' => $rx->id,
            'drug_name'           => 'Cetirizine 10mg',
            'dose'                => '1 tab',
            'frequency'           => 'OD',
            'duration_days'       => 5,
            'route'               => 'oral',
            'instructions'        => 'At night',
            'sequence'            => 2,
            'created_by'          => $this->actorId,
            'updated_by'          => $this->actorId,
            'status_flag'         => 1,
            'sort_order'          => 1,
            'status'              => 1,
        ]);

        app(OpdVisitRepository::class)->logAudit(
            $visit,
            OpdVisitActionEnum::PRESCRIPTION_SAVED,
            null,
            null,
            $this->actorId,
            'Prescription recorded',
            ['opd_prescription_id' => $rx->id, 'items' => 2],
        );
    }

    /* ---------------------------------------------------------------------- */
    /* Investigation order                                                    */
    /* ---------------------------------------------------------------------- */

    private function createInvestigationOrder(OpdVisit $visit): void
    {
        // Migration columns for opd_investigation_orders: ordered_by,
        // ordered_at, status, notes, clinical_indication.
        // For opd_investigation_order_items: lab_test_id, test_name_snapshot,
        // price_snapshot, sequence.
        $order = OpdInvestigationOrder::query()->forceCreate([
            'opd_visit_id'        => $visit->id,
            'patient_id'          => $visit->patient_id,
            'ordered_by'          => $this->actorId,
            'ordered_at'          => now(),
            'status'              => 'ordered',
            'clinical_indication' => 'Workup for acute febrile illness',
            'notes'               => null,
            'created_by'          => $this->actorId,
            'updated_by'          => $this->actorId,
            'status_flag'         => 1,
            'sort_order'          => 0,
        ]);

        $cbc   = LabTest::query()->find($this->cbcLabTestId);
        $lipid = LabTest::query()->find($this->lipidLabTestId);

        OpdInvestigationOrderItem::query()->forceCreate([
            'opd_investigation_order_id' => $order->id,
            'lab_test_id'                => $this->cbcLabTestId,
            'test_name_snapshot'         => $cbc?->name ?? 'Complete Blood Count',
            'price_snapshot'             => $cbc?->default_price ?? 350.00,
            'sequence'                   => 1,
            'created_by'                 => $this->actorId,
            'updated_by'                 => $this->actorId,
            'status_flag'                => 1,
            'sort_order'                 => 0,
            'status'                     => 1,
        ]);

        OpdInvestigationOrderItem::query()->forceCreate([
            'opd_investigation_order_id' => $order->id,
            'lab_test_id'                => $this->lipidLabTestId,
            'test_name_snapshot'         => $lipid?->name ?? 'Lipid Profile',
            'price_snapshot'             => $lipid?->default_price ?? 900.00,
            'sequence'                   => 2,
            'created_by'                 => $this->actorId,
            'updated_by'                 => $this->actorId,
            'status_flag'                => 1,
            'sort_order'                 => 1,
            'status'                     => 1,
        ]);

        app(OpdVisitRepository::class)->logAudit(
            $visit,
            OpdVisitActionEnum::ORDER_SAVED,
            null,
            null,
            $this->actorId,
            'Investigation order recorded',
            ['opd_investigation_order_id' => $order->id, 'items' => 2],
        );
    }

    /* ---------------------------------------------------------------------- */
    /* Visit lifecycle: vitals_taken -> in_consultation -> completed         */
    /* ---------------------------------------------------------------------- */

    private function advanceVisit(OpdVisit $visit): void
    {
        $visitRepo = app(OpdVisitRepository::class);

        $visitRepo->transitionStatus(
            $visit->id,
            OpdVisitStatusEnum::IN_CONSULTATION,
            $this->actorId,
            'Doctor started consultation',
            [],
            OpdVisitActionEnum::STATUS_CHANGE,
        );

        $visitRepo->transitionStatus(
            $visit->id,
            OpdVisitStatusEnum::COMPLETED,
            $this->actorId,
            'Consultation completed; prescription and investigation order placed',
            [],
            OpdVisitActionEnum::STATUS_CHANGE,
        );
    }

    /* ---------------------------------------------------------------------- */
    /* Summary                                                                */
    /* ---------------------------------------------------------------------- */

    private function printSummary(OpdVisit $visit): void
    {
        $this->command->info('--- OPD demo summary ---');
        $this->command->info("Visit:   {$visit->opd_no} (id={$visit->id}) status={$visit->status}");
        $this->command->info("Patient: id={$this->patientOneId}");
        $this->command->info("Doctor:  user_id={$this->actorId} employee_id={$this->doctorEmployeeId}");
        $this->command->info("Dept:    id={$this->departmentId}");

        $vitals = OpdVital::query()->where('opd_visit_id', $visit->id)->count();
        $dx     = OpdDiagnosis::query()->where('opd_visit_id', $visit->id)->count();
        $rx     = OpdPrescription::query()->where('opd_visit_id', $visit->id)->count();
        $rxItems= OpdPrescriptionItem::query()
            ->whereHas('prescription', function ($q) use ($visit) {
                $q->where('opd_visit_id', $visit->id);
            })
            ->count();
        $orders = OpdInvestigationOrder::query()->where('opd_visit_id', $visit->id)->count();
        $audit  = DB::table('opd_visit_audit_logs')->where('opd_visit_id', $visit->id)->count();

        $this->command->info("Vitals:  {$vitals}");
        $this->command->info("Diagnoses: {$dx}");
        $this->command->info("Prescriptions: {$rx} (items: {$rxItems})");
        $this->command->info("Investigation orders: {$orders}");
        $this->command->info("Audit log rows: {$audit}");
        $this->command->info('------------------------');
    }
}
