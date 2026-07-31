<?php

namespace Database\Seeders;

use App\Models\AppointmentSlot;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Drug;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleSlot;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\LeaveType;
use App\Models\Logistic;
use App\Models\Role;
use App\Models\Shelve;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\Theatre;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Master/reference data that almost every other demo seeder depends on:
 * branches, departments, designations, item catalogue (categories/brands/
 * units/items), suppliers, shelves, wards/beds, theatres, shifts, leave
 * types, a roster of doctors + support staff (users + employees), doctor
 * schedules/slots, and a rolling 14-day window of appointment slots.
 *
 * Idempotent: every entity is find-or-create keyed by a natural/unique key,
 * safe to re-run. Run this before any other *DemoSeeder.
 */
class MasterDataDemoSeeder extends Seeder
{
    /** @var array<string,int> department name => id */
    private array $departments = [];

    /** @var array<int,array{user_id:int, employee_id:int, department:string}> doctors */
    private array $doctors = [];

    private int $branchMainId;
    private int $branchCityId;
    private int $warehouseId;
    private int $logisticId;

    public function run(): void
    {
        $this->command->info('[MasterDataDemoSeeder] Starting master data seed ...');

        $this->seedBranches();
        $this->seedDepartments();
        $this->seedDesignations();
        $this->seedItemCatalogue();
        $this->seedDrugCatalog();
        $this->seedSuppliers();
        $this->seedShelves();
        $this->seedWardsBeds();
        $this->seedTheatres();
        $this->seedShiftsLeaveTypes();
        $this->seedDoctorsAndStaff();
        $this->seedDoctorSchedules();
        $this->seedAppointmentSlots();

        $this->command->info('[MasterDataDemoSeeder] Done.');
    }

    private function seedBranches(): void
    {
        $main = Branch::query()->firstOrCreate(
            ['name' => 'Main Hospital Campus'],
            ['type' => 'Branch', 'address' => 'Plot 1, Hospital Road, Dhaka', 'status' => 1],
        );
        $this->branchMainId = $main->id;

        $city = Branch::query()->firstOrCreate(
            ['name' => 'City Extension Branch'],
            ['type' => 'Branch', 'address' => '45 Gulshan Avenue, Dhaka', 'status' => 1],
        );
        $this->branchCityId = $city->id;

        $wh = Branch::query()->firstOrCreate(
            ['name' => 'Central Pharmacy Warehouse'],
            ['type' => 'Warehouse', 'address' => 'Industrial Area, Tongi', 'status' => 1],
        );
        $this->warehouseId = $wh->id;

        $logistic = Logistic::query()->first();
        $this->logisticId = $logistic?->id ?? 1;
    }

    private function seedDepartments(): void
    {
        $names = [
            'General OPD', 'Cardiology', 'Orthopedics', 'Pediatrics',
            'Gynecology & Obstetrics', 'ENT', 'Dermatology', 'Internal Medicine',
            'Emergency Medicine', 'General Surgery', 'Nephrology', 'Neurology',
        ];

        foreach ($names as $name) {
            $dept = Department::query()->updateOrCreate(
                ['name' => $name],
                ['description' => "{$name} department (demo)", 'status' => 1],
            );
            $this->departments[$name] = $dept->id;
        }
    }

    private function seedDesignations(): void
    {
        $titles = [
            ['title' => 'Senior Consultant', 'grade' => 'Grade 1'],
            ['title' => 'Consultant', 'grade' => 'Grade 2'],
            ['title' => 'Medical Officer', 'grade' => 'Grade 3'],
            ['title' => 'Staff Nurse', 'grade' => 'Grade 4'],
            ['title' => 'Senior Staff Nurse', 'grade' => 'Grade 3'],
            ['title' => 'Pharmacist', 'grade' => 'Grade 4'],
            ['title' => 'Lab Technician', 'grade' => 'Grade 4'],
            ['title' => 'Radiographer', 'grade' => 'Grade 4'],
            ['title' => 'Receptionist', 'grade' => 'Grade 5'],
            ['title' => 'Ward Boy', 'grade' => 'Grade 5'],
            ['title' => 'Accountant', 'grade' => 'Grade 4'],
            ['title' => 'HR Executive', 'grade' => 'Grade 4'],
        ];

        foreach ($titles as $row) {
            Designation::query()->updateOrCreate(
                ['title' => $row['title']],
                ['grade' => $row['grade'], 'status' => 1],
            );
        }
    }

    private function seedItemCatalogue(): void
    {
        foreach (['General', 'Pharmacy', 'Surgical', 'Consumable', 'Laboratory'] as $cat) {
            ItemCategory::query()->updateOrCreate(['name' => $cat], ['status' => 1]);
        }
        foreach (['Generic', 'Square', 'Beximco', 'Incepta', 'ACI', 'Renata'] as $brand) {
            Brand::query()->updateOrCreate(['name' => $brand], ['status' => 1]);
        }
        foreach ([['Piece', 'pcs'], ['Box', 'box'], ['Strip', 'strip'], ['Bottle', 'btl'], ['Vial', 'vial']] as [$name, $short]) {
            Unit::query()->updateOrCreate(['name' => $name], ['short_name' => $short, 'status' => 1]);
        }

        $catId   = ItemCategory::query()->where('name', 'Pharmacy')->value('id');
        $consCat = ItemCategory::query()->where('name', 'Consumable')->value('id');
        $brandId = Brand::query()->where('name', 'Square')->value('id');
        $unitId  = Unit::query()->where('name', 'Strip')->value('id');
        $pcsUnit = Unit::query()->where('name', 'Piece')->value('id');

        $drugs = [
            ['Paracetamol 500mg', 'PARA-500'],
            ['Amoxicillin 250mg', 'AMOX-250'],
            ['Cetirizine 10mg', 'CETI-10'],
            ['Omeprazole 20mg', 'OMEP-20'],
            ['Metformin 500mg', 'METF-500'],
            ['Atorvastatin 10mg', 'ATOR-10'],
            ['Losartan 50mg', 'LOSA-50'],
            ['Azithromycin 500mg', 'AZIT-500'],
            ['Ibuprofen 400mg', 'IBUP-400'],
            ['Insulin Glargine', 'INSU-GLA'],
        ];
        foreach ($drugs as [$name, $code]) {
            Item::query()->updateOrCreate(
                ['code' => $code],
                [
                    'type'             => 'CONSUMABLE',
                    'logistic_id'      => $this->logisticId,
                    'item_category_id' => $catId,
                    'brand_id'         => $brandId,
                    'name_en'          => $name,
                    'name_bn'          => $name,
                    'base_unit_id'     => $unitId,
                    'reorder_qty'      => 100,
                    'status'           => 1,
                ],
            );
        }

        $consumables = [
            ['Disposable Syringe 5ml', 'SYR-5ML'],
            ['Surgical Gloves (pair)', 'GLOV-SURG'],
            ['IV Cannula 20G', 'CANN-20G'],
            ['Gauze Roll', 'GAUZE-RL'],
            ['N95 Mask', 'MASK-N95'],
        ];
        foreach ($consumables as [$name, $code]) {
            Item::query()->updateOrCreate(
                ['code' => $code],
                [
                    'type'             => 'CONSUMABLE',
                    'logistic_id'      => $this->logisticId,
                    'item_category_id' => $consCat,
                    'brand_id'         => $brandId,
                    'name_en'          => $name,
                    'name_bn'          => $name,
                    'base_unit_id'     => $pcsUnit,
                    'reorder_qty'      => 200,
                    'status'           => 1,
                ],
            );
        }
    }

    /**
     * Drug-master rows over the pharmacy items so the prescription
     * typeahead, interaction checks, and recent-drugs features have a
     * catalog to link against (drugs.item_id → items).
     */
    private function seedDrugCatalog(): void
    {
        $drugs = [
            // [item code, generic, brand, strength, form, controlled]
            ['PARA-500', 'Paracetamol', 'Napa', '500mg', 'tablet', false],
            ['AMOX-250', 'Amoxicillin', 'Moxacil', '250mg', 'capsule', false],
            ['CETI-10', 'Cetirizine', 'Alatrol', '10mg', 'tablet', false],
            ['OMEP-20', 'Omeprazole', 'Seclo', '20mg', 'capsule', false],
            ['METF-500', 'Metformin', 'Comet', '500mg', 'tablet', false],
            ['ATOR-10', 'Atorvastatin', 'Atova', '10mg', 'tablet', false],
            ['LOSA-50', 'Losartan', 'Losectil', '50mg', 'tablet', false],
            ['AZIT-500', 'Azithromycin', 'Zimax', '500mg', 'tablet', false],
            ['IBUP-400', 'Ibuprofen', 'Profen', '400mg', 'tablet', false],
            ['INSU-GLA', 'Insulin Glargine', 'Lantus', '100IU/ml', 'injection', true],
        ];

        foreach ($drugs as [$code, $generic, $brand, $strength, $form, $controlled]) {
            $itemId = Item::query()->where('code', $code)->value('id');
            if (!$itemId) {
                continue;
            }

            Drug::query()->updateOrCreate(
                ['item_id' => $itemId],
                [
                    'generic_name'        => $generic,
                    'brand_name'          => $brand,
                    'strength'            => $strength,
                    'dosage_form'         => $form,
                    'is_controlled'       => $controlled,
                    'controlled_schedule' => $controlled ? 'H' : null,
                    'status'              => 1,
                ],
            );
        }
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            ['SUP-001', 'MediSource Pharma Ltd.', '+8801811100001', 'sales@medisource.example'],
            ['SUP-002', 'HealthLine Distributors', '+8801811100002', 'contact@healthline.example'],
            ['SUP-003', 'CareWell Surgical Supplies', '+8801811100003', 'info@carewell.example'],
            ['SUP-004', 'National Pharma Trading', '+8801811100004', 'trade@natpharma.example'],
        ];
        foreach ($suppliers as [$no, $name, $phone, $email]) {
            Supplier::query()->updateOrCreate(
                ['supplier_no' => $no],
                ['supplier_name' => $name, 'phone' => $phone, 'email' => $email, 'address' => 'Dhaka, Bangladesh', 'status' => 1],
            );
        }
    }

    private function seedShelves(): void
    {
        foreach (['Shelf A1', 'Shelf A2', 'Shelf B1', 'Cold Storage 1'] as $name) {
            Shelve::query()->updateOrCreate(
                ['name' => $name, 'branch_id' => $this->warehouseId],
                ['status' => 1],
            );
        }
    }

    private function seedWardsBeds(): void
    {
        $wards = [
            ['General Ward', 'general', 20],
            ['Cardiac Care Unit', 'icu', 10],
            ['Maternity Ward', 'maternity', 12],
            ['Pediatric Ward', 'pediatric', 8],
        ];

        foreach ($wards as [$name, $type, $bedCount]) {
            $ward = Ward::query()->updateOrCreate(
                ['name' => $name],
                ['branch_id' => $this->branchMainId, 'ward_type' => $type, 'floor' => '2', 'status' => 1],
            );

            for ($i = 1; $i <= $bedCount; $i++) {
                $bedNo = strtoupper(substr($type, 0, 3)) . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                Bed::query()->updateOrCreate(
                    ['ward_id' => $ward->id, 'bed_number' => $bedNo],
                    ['bed_type' => 'standard', 'daily_rate' => 1500, 'bed_status' => 'vacant', 'status' => 1],
                );
            }
        }
    }

    private function seedTheatres(): void
    {
        foreach (['OT-1 Main Theatre', 'OT-2 Minor Procedures', 'OT-3 Cardiac Theatre'] as $i => $name) {
            Theatre::query()->updateOrCreate(
                ['name' => $name],
                ['branch_id' => $this->branchMainId, 'floor' => '3', 'theatre_type' => $i === 2 ? 'cardiac' : 'general', 'status' => 1],
            );
        }
    }

    private function seedShiftsLeaveTypes(): void
    {
        $shifts = [
            ['Morning Shift', '08:00:00', '16:00:00'],
            ['Evening Shift', '16:00:00', '00:00:00'],
            ['Night Shift', '00:00:00', '08:00:00'],
        ];
        foreach ($shifts as [$name, $start, $end]) {
            Shift::query()->updateOrCreate(['name' => $name], ['start_time' => $start, 'end_time' => $end, 'status' => 1]);
        }

        $leaveTypes = [
            ['Casual Leave', 10, true],
            ['Sick Leave', 14, true],
            ['Earned Leave', 20, true],
            ['Unpaid Leave', 0, false],
        ];
        foreach ($leaveTypes as [$name, $max, $paid]) {
            LeaveType::query()->updateOrCreate(['name' => $name], ['max_days_per_year' => $max, 'is_paid' => $paid, 'status' => 1]);
        }
    }

    private function seedDoctorsAndStaff(): void
    {
        $doctorRoster = [
            ['Dr. Anisur Rahman', 'male', 'Cardiology'],
            ['Dr. Farhana Islam', 'female', 'Pediatrics'],
            ['Dr. Kamal Hossain', 'male', 'Orthopedics'],
            ['Dr. Nasrin Akter', 'female', 'Gynecology & Obstetrics'],
            ['Dr. Shahidul Alam', 'male', 'ENT'],
            ['Dr. Tania Sultana', 'female', 'Dermatology'],
            ['Dr. Rezaul Karim', 'male', 'Internal Medicine'],
            ['Dr. Mahmuda Khatun', 'female', 'General Surgery'],
            ['Dr. Imran Chowdhury', 'male', 'Nephrology'],
            ['Dr. Salma Begum', 'female', 'Neurology'],
        ];

        $seniorDesignationId = Designation::query()->where('title', 'Senior Consultant')->value('id');
        // Doctor Schedule now keys doctor_id to users; doctor users must carry
        // the Doctor role so the /user/doctors picker lists them.
        $doctorRoleId = Role::query()->where('name', 'Doctor')->value('id');

        foreach ($doctorRoster as $i => [$name, $gender, $dept]) {
            $slug  = 'doctor' . ($i + 2); // doctor1 already used by OpdDemoSeeder's doctor@hms.local
            $email = "{$slug}@hms.local";
            $phone = '+88017300' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $name, 'last_name' => '', 'name' => $name, 'user_type' => 'employee',
                    // Plain text — the User model's saving hook hashes it. Passing an
                    // already-hashed value here would double-hash and lock the user out.
                    'phone' => $phone, 'password' => 'password',
                    'role_ids' => $doctorRoleId ? [(string) $doctorRoleId] : [],
                    'department_id' => $this->departments[$dept] ?? null,
                    'web_access' => 1, 'app_access' => 1, 'is_verified' => 1, 'status' => 1,
                ],
            );

            // Keyed by employee_id (the stable natural key), not user_id: AuthSeeder
            // truncates `users` on every full db:seed run, so user_id is not a safe
            // idempotency key across runs — this also self-heals any employee row
            // left pointing at a since-truncated user id from a prior run.
            $employee = Employee::query()->updateOrCreate(
                ['employee_id' => 'EMP-DOC-' . str_pad((string) ($i + 2), 3, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $user->id,
                    'name_en' => $name, 'gender' => $gender, 'mobile' => $phone,
                    'employee_type' => 'doctor', 'designation_id' => $seniorDesignationId,
                    'joining_date' => Carbon::now()->subYears(rand(1, 8))->toDateString(),
                    'status' => 1,
                ],
            );

            $this->doctors[] = ['user_id' => $user->id, 'employee_id' => $employee->id, 'department' => $dept];
        }

        // Support staff: nurses, pharmacists, lab techs, radiographers, receptionists.
        $support = [
            ['Nasima Sultana', 'female', 'nurse', 'Staff Nurse'],
            ['Rubel Hossain', 'male', 'nurse', 'Staff Nurse'],
            ['Shirin Akter', 'female', 'nurse', 'Senior Staff Nurse'],
            ['Jahangir Alam', 'male', 'pharmacist', 'Pharmacist'],
            ['Moushumi Rahman', 'female', 'lab_technician', 'Lab Technician'],
            ['Foysal Ahmed', 'male', 'lab_technician', 'Lab Technician'],
            ['Ruma Khatun', 'female', 'radiographer', 'Radiographer'],
            ['Aminul Islam', 'male', 'receptionist', 'Receptionist'],
            ['Nazma Begum', 'female', 'accountant', 'Accountant'],
            ['Sohel Rana', 'male', 'hr', 'HR Executive'],
        ];

        foreach ($support as $i => [$name, $gender, $type, $designationTitle]) {
            $slug  = 'staff' . ($i + 1);
            $email = "{$slug}@hms.local";
            $phone = '+88017400' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $name, 'last_name' => '', 'name' => $name, 'user_type' => 'employee',
                    'phone' => $phone, 'password' => 'password', // hashed by the User model saving hook
                    'web_access' => 1, 'app_access' => 1, 'is_verified' => 1, 'status' => 1,
                ],
            );

            $designationId = Designation::query()->where('title', $designationTitle)->value('id');

            Employee::query()->updateOrCreate(
                ['employee_id' => 'EMP-STF-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $user->id,
                    'name_en' => $name, 'gender' => $gender, 'mobile' => $phone,
                    'employee_type' => $type, 'designation_id' => $designationId,
                    'joining_date' => Carbon::now()->subYears(rand(1, 6))->toDateString(),
                    'status' => 1,
                ],
            );
        }
    }

    private function seedDoctorSchedules(): void
    {
        foreach ($this->doctors as $doc) {
            $deptId = $this->departments[$doc['department']] ?? null;

            $schedule = DoctorSchedule::query()->updateOrCreate(
                ['doctor_id' => $doc['user_id'], 'is_default' => true],
                [
                    'department_id' => $deptId,
                    'name' => 'Regular OPD Schedule',
                    'schedule_type' => 'regular',
                    'consultation_mode' => 'in_person',
                    'effective_from' => Carbon::now()->subMonths(6)->toDateString(),
                    'slot_duration_minutes' => 15,
                    'max_patients_per_slot' => 1,
                    'consultation_fee' => 800,
                    'follow_up_fee' => 500,
                    'is_recurring' => true,
                    'allow_online_booking' => true,
                    'allow_walk_in' => true,
                    'status' => 1,
                ],
            );

            // DoctorScheduleSlot's table has no deleted_at column despite the model using
            // SoftDeletes, which breaks updateOrCreate()'s implicit query scope. Existence
            // check via the query builder (no soft-delete scope) instead.
            foreach ([1, 2, 3, 4, 5] as $dow) { // Mon-Fri
                foreach ([['Morning', '09:00:00', '13:00:00'], ['Evening', '17:00:00', '20:00:00']] as [$label, $start, $end]) {
                    $exists = \Illuminate\Support\Facades\DB::table('doctor_schedule_slots')
                        ->where(['doctor_schedule_id' => $schedule->id, 'day_of_week' => $dow, 'session_label' => $label])
                        ->exists();
                    if (!$exists) {
                        DoctorScheduleSlot::query()->create([
                            'doctor_schedule_id' => $schedule->id, 'day_of_week' => $dow, 'session_label' => $label,
                            'start_time' => $start, 'end_time' => $end, 'is_active' => true, 'status' => 1,
                        ]);
                    }
                }
            }
        }
    }

    private function seedAppointmentSlots(): void
    {
        foreach ($this->doctors as $doc) {
            $deptId = $this->departments[$doc['department']] ?? null;

            for ($d = 0; $d < 14; $d++) {
                $date = Carbon::today()->addDays($d);
                if ($date->isWeekend()) {
                    continue;
                }

                foreach ([['09:00:00', '13:00:00'], ['17:00:00', '20:00:00']] as [$start, $end]) {
                    $startAt = $date->copy()->setTimeFromTimeString($start);
                    $endAt   = $date->copy()->setTimeFromTimeString($end);

                    AppointmentSlot::query()->updateOrCreate(
                        ['doctor_id' => $doc['user_id'], 'slot_date' => $date->toDateString(), 'start_time' => $start],
                        [
                            'department_id' => $deptId,
                            'end_time' => $end,
                            'slot_start_at' => $startAt,
                            'slot_end_at' => $endAt,
                            'max_patients' => 16,
                            'status' => 'open',
                            'status_active' => 1,
                        ],
                    );
                }
            }
        }
    }
}
