<?php

namespace Database\Seeders;

use App\Models\RadiologyTest;
use Illuminate\Database\Seeder;

class RadiologyTestSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'RAD-001', 'name' => 'X-Ray Chest PA View',         'modality' => 'xray',        'body_part' => 'Chest',        'tat_hours' => 2,  'default_price' => 500.00],
            ['code' => 'RAD-002', 'name' => 'X-Ray Abdomen Erect',         'modality' => 'xray',        'body_part' => 'Abdomen',      'tat_hours' => 2,  'default_price' => 500.00],
            ['code' => 'RAD-003', 'name' => 'X-Ray Knee (Both)',           'modality' => 'xray',        'body_part' => 'Knee',         'tat_hours' => 2,  'default_price' => 600.00],
            ['code' => 'RAD-004', 'name' => 'Ultrasound Abdomen + Pelvis', 'modality' => 'ultrasound',  'body_part' => 'Abdomen',      'tat_hours' => 4,  'default_price' => 1800.00],
            ['code' => 'RAD-005', 'name' => 'Ultrasound Pregnancy Profile','modality' => 'ultrasound',  'body_part' => 'Obstetric',    'tat_hours' => 4,  'default_price' => 2000.00],
            ['code' => 'RAD-006', 'name' => 'CT Scan Brain (Plain)',       'modality' => 'ct',          'body_part' => 'Brain',        'tat_hours' => 12, 'default_price' => 4500.00],
            ['code' => 'RAD-007', 'name' => 'CT Scan Chest (Contrast)',    'modality' => 'ct',          'body_part' => 'Chest',        'tat_hours' => 24, 'default_price' => 7000.00],
            ['code' => 'RAD-008', 'name' => 'MRI Spine Lumbar',            'modality' => 'mri',         'body_part' => 'Lumbar Spine', 'tat_hours' => 48, 'default_price' => 7500.00],
            ['code' => 'RAD-009', 'name' => 'MRI Brain (Plain)',           'modality' => 'mri',         'body_part' => 'Brain',        'tat_hours' => 48, 'default_price' => 8000.00],
            ['code' => 'RAD-010', 'name' => 'Mammography (Bilateral)',     'modality' => 'mammography', 'body_part' => 'Breast',       'tat_hours' => 24, 'default_price' => 2500.00],
        ];

        foreach ($rows as $r) {
            RadiologyTest::updateOrCreate(['code' => $r['code']], $r);
        }
    }
}
