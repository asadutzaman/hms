<?php

namespace App\Console\Commands;

use App\Services\Appointment\AppointmentReminderService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'hms:send-appointment-reminders';

    protected $description = 'Send SMS/email reminders for appointments due in ~24h and ~2h (F-02-05)';

    public function handle(AppointmentReminderService $service)
    {
        $result = $service->sendDueReminders();

        $this->info("24h reminders sent: {$result['24h']}");
        $this->info("2h reminders sent: {$result['2h']}");

        return self::SUCCESS;
    }
}
