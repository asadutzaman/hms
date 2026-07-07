<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'key' => 'critical_lab_value.in_app',
                'name' => 'Critical Lab Value — In-App',
                'channel' => 'in_app',
                'subject_template' => null,
                'body_template' => 'CRITICAL RESULT: {{test_name}} for {{patient_name}} (Order {{lab_order_no}}) requires immediate attention.',
            ],
            [
                'key' => 'critical_lab_value.email',
                'name' => 'Critical Lab Value — Email',
                'channel' => 'email',
                'subject_template' => 'Critical Lab Value Alert — {{lab_order_no}}',
                'body_template' => "A critical lab result has been entered.\n\nPatient: {{patient_name}}\nTest: {{test_name}}\nOrder No: {{lab_order_no}}\n\nPlease review immediately.",
            ],
            [
                'key' => 'appointment_reminder_24h.sms',
                'name' => 'Appointment Reminder (24h) — SMS',
                'channel' => 'sms',
                'subject_template' => null,
                'body_template' => 'Reminder: {{patient_name}}, your appointment ({{appointment_no}}) is tomorrow {{appointment_date}} at {{appointment_time}}.',
            ],
            [
                'key' => 'appointment_reminder_24h.email',
                'name' => 'Appointment Reminder (24h) — Email',
                'channel' => 'email',
                'subject_template' => 'Appointment Reminder — {{appointment_date}}',
                'body_template' => "Dear {{patient_name}},\n\nThis is a reminder that your appointment ({{appointment_no}}) is scheduled for {{appointment_date}} at {{appointment_time}}.\n\nThank you.",
            ],
            [
                'key' => 'appointment_reminder_2h.sms',
                'name' => 'Appointment Reminder (2h) — SMS',
                'channel' => 'sms',
                'subject_template' => null,
                'body_template' => 'Reminder: {{patient_name}}, your appointment ({{appointment_no}}) is today at {{appointment_time}}. Please arrive on time.',
            ],
            [
                'key' => 'appointment_reminder_2h.email',
                'name' => 'Appointment Reminder (2h) — Email',
                'channel' => 'email',
                'subject_template' => 'Appointment Today — {{appointment_time}}',
                'body_template' => "Dear {{patient_name}},\n\nThis is a reminder that your appointment ({{appointment_no}}) is today at {{appointment_time}}. Please arrive on time.\n\nThank you.",
            ],
            [
                'key' => 'patient_login_otp.email',
                'name' => 'Patient Portal Login OTP — Email',
                'channel' => 'email',
                'subject_template' => 'Your login code: {{otp_code}}',
                'body_template' => "Dear {{patient_name}},\n\nYour login code for the patient portal is: {{otp_code}}\n\nThis code expires in {{expires_in}} minutes. If you did not request this, please ignore this email.",
            ],
        ];

        foreach ($rows as $r) {
            NotificationTemplate::updateOrCreate(['key' => $r['key']], $r + ['is_active' => true]);
        }
    }
}
