<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Patient;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Volume for two independent slices: a larger patient registry (for OPD/IPD/
 * ER/lab/radiology seeders to draw from) and HR data (attendance across a
 * rolling window + leave requests in varied approval states) so the
 * shift-wise attendance report and leave dashboard have something to show.
 *
 * Idempotent: patients keyed by `mrn`, attendance keyed by
 * (employee_id, attendance_date), leave requests keyed by `request_no`.
 */
class PatientHrDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('[PatientHrDemoSeeder] Starting ...');
        $this->seedPatients();
        $this->seedAttendance();
        $this->seedLeaveRequests();
        $this->command->info('[PatientHrDemoSeeder] Done.');
    }

    private function seedPatients(): void
    {
        $first = ['Rahim', 'Karima', 'Jashim', 'Mou', 'Habib', 'Nusrat', 'Delwar', 'Shirin', 'Faruk', 'Rina',
            'Anwar', 'Poly', 'Shakil', 'Ruma', 'Nazrul', 'Sultana', 'Bappy', 'Lima', 'Milton', 'Jui',
            'Rafiq', 'Shanta', 'Iqbal', 'Nipa', 'Zahid'];
        $last  = ['Uddin', 'Begum', 'Islam', 'Akter', 'Rahman', 'Khatun', 'Chowdhury', 'Hossain', 'Ahmed', 'Sultana'];
        $genders = ['male', 'female'];
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $cities = ['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna'];

        for ($i = 1; $i <= 25; $i++) {
            $mrn = 'MRN-DEMO-' . str_pad((string) ($i + 100), 4, '0', STR_PAD_LEFT);
            $fn  = $first[$i % count($first)];
            $ln  = $last[$i % count($last)];
            $gender = $genders[$i % 2];
            $dob = Carbon::now()->subYears(rand(2, 78))->subDays(rand(0, 364));

            Patient::query()->updateOrCreate(
                ['mrn' => $mrn],
                [
                    'first_name' => $fn,
                    'last_name'  => $ln,
                    'date_of_birth' => $dob->toDateString(),
                    'gender' => $gender,
                    'blood_group' => $bloodGroups[$i % count($bloodGroups)],
                    'marital_status' => $dob->age >= 22 ? 'married' : 'single',
                    'primary_phone' => '+8801' . rand(600000000, 999999999),
                    'current_address' => 'House ' . rand(1, 99) . ', Road ' . rand(1, 20) . ', ' . $cities[$i % count($cities)],
                    'current_city' => $cities[$i % count($cities)],
                    'current_country' => 'Bangladesh',
                    'registration_date' => Carbon::now()->subDays(rand(0, 200))->toDateString(),
                    'status' => 1,
                ],
            );
        }

        $this->command->info('[PatientHrDemoSeeder] Patients now: ' . Patient::query()->count());
    }

    private function seedAttendance(): void
    {
        $employees = Employee::query()->pluck('id')->all();
        $shiftId   = Shift::query()->where('name', 'Morning Shift')->value('id');

        $created = 0;
        foreach ($employees as $employeeId) {
            for ($d = 30; $d >= 1; $d--) {
                $date = Carbon::today()->subDays($d);
                if ($date->isFriday()) { // BD weekend
                    continue;
                }

                $exists = DB::table('attendance_records')
                    ->where(['employee_id' => $employeeId, 'attendance_date' => $date->toDateString()])
                    ->exists();
                if ($exists) {
                    continue;
                }

                // ~5% absent, rest present (checked in/out).
                $isAbsent = rand(1, 100) <= 5;
                $checkIn  = $isAbsent ? null : $date->copy()->setTime(8, rand(0, 45), 0);
                $checkOut = $isAbsent ? null : $date->copy()->setTime(16, rand(0, 45), 0);

                AttendanceRecord::query()->create([
                    'employee_id' => $employeeId,
                    'attendance_date' => $date->toDateString(),
                    'shift_id' => $shiftId,
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'work_hours' => $isAbsent ? 0 : 8,
                    'source' => 'manual',
                    'remarks' => $isAbsent ? 'Absent' : null,
                    'status' => 1,
                ]);
                $created++;
            }
        }

        $this->command->info("[PatientHrDemoSeeder] Attendance rows created: {$created}");
    }

    private function seedLeaveRequests(): void
    {
        $employees = Employee::query()->pluck('id')->take(10)->all();
        $leaveTypeId = LeaveType::query()->where('name', 'Casual Leave')->value('id');
        $sickTypeId  = LeaveType::query()->where('name', 'Sick Leave')->value('id');
        $statuses = ['SUBMITTED', 'APPROVED', 'APPROVED', 'REJECTED'];

        foreach ($employees as $i => $employeeId) {
            $requestNo = 'LR-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            $start = Carbon::today()->addDays(rand(-20, 20));
            $days  = rand(1, 3);
            $end   = $start->copy()->addDays($days - 1);
            $status = $statuses[$i % count($statuses)];

            LeaveRequest::query()->updateOrCreate(
                ['request_no' => $requestNo],
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $i % 3 === 0 ? $sickTypeId : $leaveTypeId,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'total_days' => $days,
                    'reason' => $i % 3 === 0 ? 'Fever and medical rest' : 'Personal work',
                    'process_status' => $status,
                    'applied_by' => $employeeId,
                    'approved_by' => $status === 'APPROVED' ? $employees[0] : null,
                    'approved_at' => $status === 'APPROVED' ? now() : null,
                    'status' => 1,
                ],
            );
        }

        $this->command->info('[PatientHrDemoSeeder] Leave requests now: ' . LeaveRequest::query()->count());
    }
}
