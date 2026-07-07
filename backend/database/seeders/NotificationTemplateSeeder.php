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
        ];

        foreach ($rows as $r) {
            NotificationTemplate::updateOrCreate(['key' => $r['key']], $r + ['is_active' => true]);
        }
    }
}
