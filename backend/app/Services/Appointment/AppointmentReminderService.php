<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentReminderService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send the 24h and 2h reminder windows — called from the scheduled
     * `hms:send-appointment-reminders` command. Idempotent via the
     * reminder_24h_sent_at/reminder_2h_sent_at columns, so re-running the
     * command (or a missed schedule tick catching up) never double-sends.
     */
    public function sendDueReminders(): array
    {
        return [
            '24h' => $this->sendWindow(24, 'reminder_24h_sent_at', 'appointment_reminder_24h'),
            '2h'  => $this->sendWindow(2, 'reminder_2h_sent_at', 'appointment_reminder_2h'),
        ];
    }

    protected function sendWindow(int $hoursBefore, string $sentColumn, string $eventKey): int
    {
        $now = now();
        $windowStart = $now->copy()->addHours($hoursBefore);
        // A 15-minute catch window so a command that only runs once per hour
        // (or is briefly delayed) still catches appointments due in this window.
        $windowEnd = $windowStart->copy()->addMinutes(15);

        $appointments = Appointment::query()
            ->with('patient')
            ->whereNull($sentColumn)
            ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
            ->whereBetween('appointment_at', [$windowStart, $windowEnd])
            ->get();

        $sentCount = 0;
        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            if (!$patient) {
                continue;
            }

            $data = [
                'patient_name'     => trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
                'appointment_date' => Carbon::parse($appointment->appointment_at)->format('Y-m-d'),
                'appointment_time' => Carbon::parse($appointment->appointment_at)->format('h:i A'),
                'appointment_no'   => $appointment->appointment_no,
            ];

            try {
                $this->notificationService->sendEventToContact(
                    $eventKey,
                    ['email' => $patient->email, 'phone' => $patient->primary_phone],
                    $data,
                    ['sms', 'email']
                );
            } catch (\Throwable $e) {
                Log::warning("Appointment reminder failed for appointment {$appointment->id}: " . $e->getMessage());
            }

            $appointment->{$sentColumn} = $now;
            $appointment->save();
            $sentCount++;
        }

        return $sentCount;
    }
}
