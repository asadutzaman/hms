<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Rolling "current" appointment data — always centred on TODAY, spanning one
 * week back to one week ahead. Re-running refreshes everything to the current
 * date (idempotent: it force-deletes its own previous rows first, which are
 * tagged with an "CUR-" appointment_no prefix).
 *
 *   php artisan db:seed --class=AppointmentCurrentDemoSeeder
 *
 * Populates: past days = completed / no-show, TODAY = active statuses
 * (checked_in / in_consultation / confirmed) so every doctor dashboard shows a
 * live queue, future days = confirmed / pending so patients have upcoming
 * visits. doctor_id = the doctor's USER id (matches the post-migration schema
 * and the existing appointment rows). NEVER run bare `php artisan db:seed`.
 */
class AppointmentCurrentDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Clear this seeder's previous output so re-runs are always current.
        Appointment::withTrashed()->where('appointment_no', 'like', 'CUR-%')->forceDelete();

        // 2) Doctors (users holding the Doctor role) + their department & fee.
        $roleId = Role::query()->where('name', 'Doctor')->value('id');
        if (!$roleId) {
            $this->command?->warn('No "Doctor" role found — nothing seeded.');
            return;
        }
        $doctors = User::query()
            ->whereJsonContains('role_ids', (string) $roleId)
            ->where('status', 1)
            ->get(['id', 'department_id']);
        if ($doctors->isEmpty()) {
            $this->command?->warn('No doctor users found — nothing seeded.');
            return;
        }

        $fees = DoctorSchedule::query()
            ->where('status', 1)
            ->get(['doctor_id', 'consultation_fee'])
            ->groupBy('doctor_id')
            ->map(fn ($rows) => (float) $rows->min('consultation_fee'));

        $patientIds = Patient::query()->where('status', 1)->orderBy('id')->limit(8)->pluck('id')->all();
        if (empty($patientIds)) {
            $this->command?->warn('No patients found — nothing seeded.');
            return;
        }

        $today = Carbon::today();
        $seq = 0;
        $tokens = [];   // per doctor+date token counter
        $created = 0;

        for ($offset = -7; $offset <= 7; $offset++) {
            $date = $today->copy()->addDays($offset);

            foreach ($doctors as $docIndex => $doctor) {
                $count = $offset === 0 ? 3 : 2; // busier "today"
                for ($i = 0; $i < $count; $i++) {
                    $seq++;
                    $patientId = $patientIds[($seq + $docIndex) % count($patientIds)];
                    $status = $this->pickStatus($offset, $i);

                    $slotStart = $date->copy()->setTime(9, 0)->addMinutes($i * 30 + $docIndex * 15);
                    $tokenKey = $doctor->id . '|' . $date->toDateString();
                    $tokens[$tokenKey] = ($tokens[$tokenKey] ?? 0) + 1;

                    $this->makeAppointment(
                        seq: $seq,
                        patientId: $patientId,
                        doctor: $doctor,
                        fee: $fees[$doctor->id] ?? 500,
                        slotStart: $slotStart,
                        token: $tokens[$tokenKey],
                        status: $status,
                    );
                    $created++;
                }
            }
        }

        // 3) Guarantee the demo patient (first patient) has a clear "next
        //    appointment" tomorrow, for the patient-home card.
        $seq++;
        $tomorrow = $today->copy()->addDay()->setTime(10, 30);
        $firstDoctor = $doctors->first();
        $this->makeAppointment(
            seq: $seq,
            patientId: $patientIds[0],
            doctor: $firstDoctor,
            fee: $fees[$firstDoctor->id] ?? 500,
            slotStart: $tomorrow,
            token: 1,
            status: 'confirmed',
        );
        $created++;

        $this->command?->info("AppointmentCurrentDemoSeeder: {$created} appointments across " . $today->copy()->subDays(7)->toDateString() . " … " . $today->copy()->addDays(7)->toDateString() . '.');
    }

    private function pickStatus(int $offset, int $i): string
    {
        if ($offset < 0) {
            // Past: mostly completed, some no-shows / cancellations.
            return match (true) {
                $i === 0 => 'completed',
                ($offset + $i) % 5 === 0 => 'no_show',
                ($offset + $i) % 7 === 0 => 'cancelled',
                default => 'completed',
            };
        }
        if ($offset === 0) {
            // Today: active statuses so doctor dashboards show a live queue.
            return ['checked_in', 'in_consultation', 'confirmed'][$i % 3];
        }
        // Future: booked/awaiting.
        return $i % 2 === 0 ? 'confirmed' : 'pending';
    }

    private function makeAppointment(int $seq, int $patientId, $doctor, float $fee, Carbon $slotStart, int $token, string $status): void
    {
        $data = [
            'appointment_no'   => 'CUR-' . str_pad((string) $seq, 5, '0', STR_PAD_LEFT),
            'patient_id'       => $patientId,
            'doctor_id'        => $doctor->id,          // USER id (see class docblock)
            'department_id'    => $doctor->department_id,
            'source'           => 'online',
            'consultation_mode' => 'in_person',
            'appointment_date' => $slotStart->toDateString(),
            'start_time'       => $slotStart->format('H:i:s'),
            'end_time'         => $slotStart->copy()->addMinutes(30)->format('H:i:s'),
            'appointment_at'   => $slotStart->copy(),
            'token_number'     => $token,
            'status'           => $status,
            'payment_status'   => $status === 'completed' ? 'paid' : 'unpaid',
            'consultation_fee' => $fee,
            'paid_amount'      => $status === 'completed' ? $fee : 0,
            'reason_for_visit' => $this->reason($seq),
            'booked_by'        => 1,
            'status_active'    => 1,
        ];

        // Status-consistent lifecycle timestamps.
        switch ($status) {
            case 'completed':
                $data['confirmed_at'] = $slotStart->copy()->subDay();
                $data['checked_in_at'] = $slotStart->copy()->subMinutes(10);
                $data['consultation_started_at'] = $slotStart->copy();
                $data['consultation_ended_at'] = $slotStart->copy()->addMinutes(20);
                $data['completed_at'] = $slotStart->copy()->addMinutes(20);
                break;
            case 'in_consultation':
                $data['confirmed_at'] = $slotStart->copy()->subDay();
                $data['checked_in_at'] = $slotStart->copy()->subMinutes(10);
                $data['consultation_started_at'] = $slotStart->copy();
                break;
            case 'checked_in':
                $data['confirmed_at'] = $slotStart->copy()->subDay();
                $data['checked_in_at'] = $slotStart->copy()->subMinutes(8);
                break;
            case 'confirmed':
                $data['confirmed_at'] = $slotStart->copy()->subDay();
                break;
            case 'cancelled':
                $data['cancelled_at'] = $slotStart->copy()->subHours(3);
                $data['cancellation_reason'] = 'Cancelled by patient (demo).';
                break;
            default:
                break;
        }

        Appointment::create($data);
    }

    private function reason(int $seq): string
    {
        $reasons = [
            'Follow-up review', 'New complaint — chest pain', 'Medication review',
            'Post-procedure check', 'Routine consultation', 'Lab result review',
            'Palpitations on exertion', 'Hypertension follow-up',
        ];
        return $reasons[$seq % count($reasons)];
    }
}
