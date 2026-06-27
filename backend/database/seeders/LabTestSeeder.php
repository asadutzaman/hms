<?php

namespace Database\Seeders;

use App\Models\LabTest;
use Illuminate\Database\Seeder;

class LabTestSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'HEM-001', 'name' => 'Complete Blood Count (CBC)',         'category' => 'haematology', 'sample_type' => 'EDTA Blood',    'tat_hours' => 4,  'default_price' => 350.00],
            ['code' => 'HEM-002', 'name' => 'Erythrocyte Sedimentation Rate (ESR)','category' => 'haematology', 'sample_type' => 'EDTA Blood',    'tat_hours' => 4,  'default_price' => 120.00],
            ['code' => 'HEM-003', 'name' => 'Haemoglobin',                       'category' => 'haematology', 'sample_type' => 'EDTA Blood',    'tat_hours' => 2,  'default_price' => 100.00],
            ['code' => 'HEM-004', 'name' => 'Platelet Count',                    'category' => 'haematology', 'sample_type' => 'EDTA Blood',    'tat_hours' => 4,  'default_price' => 200.00],
            ['code' => 'HEM-005', 'name' => 'Peripheral Blood Smear',            'category' => 'haematology', 'sample_type' => 'EDTA Blood',    'tat_hours' => 6,  'default_price' => 250.00],
            ['code' => 'HEM-006', 'name' => 'Prothrombin Time (PT/INR)',         'category' => 'haematology', 'sample_type' => 'Citrate Plasma','tat_hours' => 6,  'default_price' => 400.00],
            ['code' => 'HEM-007', 'name' => 'APTT',                              'category' => 'haematology', 'sample_type' => 'Citrate Plasma','tat_hours' => 6,  'default_price' => 400.00],

            // ---------------- Biochemistry ----------------
            ['code' => 'BIO-001', 'name' => 'Fasting Blood Sugar (FBS)',         'category' => 'biochemistry','sample_type' => 'Fluoride Plasma','tat_hours' => 4,  'default_price' => 150.00],
            ['code' => 'BIO-002', 'name' => 'Post-Prandial Blood Sugar (PPBS)',  'category' => 'biochemistry','sample_type' => 'Fluoride Plasma','tat_hours' => 4,  'default_price' => 150.00],
            ['code' => 'BIO-003', 'name' => 'HbA1c (Glycated Haemoglobin)',      'category' => 'biochemistry','sample_type' => 'EDTA Blood',    'tat_hours' => 24, 'default_price' => 800.00],
            ['code' => 'BIO-004', 'name' => 'Lipid Profile',                     'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 8,  'default_price' => 900.00],
            ['code' => 'BIO-005', 'name' => 'Liver Function Test (LFT)',         'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 8,  'default_price' => 850.00],
            ['code' => 'BIO-006', 'name' => 'Renal Function Test (RFT)',         'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 8,  'default_price' => 750.00],
            ['code' => 'BIO-007', 'name' => 'Serum Electrolytes (Na/K/Cl)',      'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 4,  'default_price' => 600.00],
            ['code' => 'BIO-008', 'name' => 'Thyroid Profile (T3/T4/TSH)',       'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 24, 'default_price' => 1100.00],
            ['code' => 'BIO-009', 'name' => 'TSH (Thyroid Stimulating Hormone)', 'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 24, 'default_price' => 600.00],
            ['code' => 'BIO-010', 'name' => 'Vitamin D (25-OH)',                 'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 48, 'default_price' => 1800.00],
            ['code' => 'BIO-011', 'name' => 'Vitamin B12',                       'category' => 'biochemistry','sample_type' => 'Serum',         'tat_hours' => 48, 'default_price' => 1500.00],

            // ---------------- Urinalysis ----------------
            ['code' => 'URN-001', 'name' => 'Urine Routine & Microscopy',         'category' => 'urine',      'sample_type' => 'Urine',         'tat_hours' => 4,  'default_price' => 200.00],
            ['code' => 'URN-002', 'name' => 'Urine Culture & Sensitivity',        'category' => 'urine',      'sample_type' => 'Urine',         'tat_hours' => 48, 'default_price' => 950.00],

            // ---------------- Microbiology ----------------
            ['code' => 'MIC-001', 'name' => 'Blood Culture & Sensitivity',        'category' => 'microbiology','sample_type' => 'Aerobic Bottle','tat_hours' => 120,'default_price' => 1500.00],
            ['code' => 'MIC-002', 'name' => 'Stool Routine',                      'category' => 'microbiology','sample_type' => 'Stool',         'tat_hours' => 6,  'default_price' => 250.00],
            ['code' => 'MIC-003', 'name' => 'Sputum AFB Smear',                   'category' => 'microbiology','sample_type' => 'Sputum',        'tat_hours' => 24, 'default_price' => 300.00],

            // ---------------- Serology ----------------
            ['code' => 'SER-001', 'name' => 'Dengue NS1 Antigen',                 'category' => 'serology',   'sample_type' => 'Serum',         'tat_hours' => 6,  'default_price' => 900.00],
            ['code' => 'SER-002', 'name' => 'Widal Test',                         'category' => 'serology',   'sample_type' => 'Serum',         'tat_hours' => 6,  'default_price' => 500.00],
            ['code' => 'SER-003', 'name' => 'CRP (C-Reactive Protein)',           'category' => 'serology',   'sample_type' => 'Serum',         'tat_hours' => 4,  'default_price' => 500.00],
            ['code' => 'SER-004', 'name' => 'RA Factor',                          'category' => 'serology',   'sample_type' => 'Serum',         'tat_hours' => 6,  'default_price' => 500.00],

            // ---------------- Radiology ----------------
            ['code' => 'RAD-001', 'name' => 'X-Ray Chest PA View',                'category' => 'radiology',  'sample_type' => 'N/A',           'tat_hours' => 4,  'default_price' => 500.00],
            ['code' => 'RAD-002', 'name' => 'X-Ray Abdomen Erect',                'category' => 'radiology',  'sample_type' => 'N/A',           'tat_hours' => 4,  'default_price' => 500.00],
            ['code' => 'RAD-003', 'name' => 'Ultrasound Abdomen + Pelvis',        'category' => 'radiology',  'sample_type' => 'N/A',           'tat_hours' => 8,  'default_price' => 1800.00],
            ['code' => 'RAD-004', 'name' => 'CT Scan Brain (Plain)',              'category' => 'radiology',  'sample_type' => 'N/A',           'tat_hours' => 24, 'default_price' => 4500.00],
            ['code' => 'RAD-005', 'name' => 'MRI Spine Lumbar',                   'category' => 'radiology',  'sample_type' => 'N/A',           'tat_hours' => 48, 'default_price' => 7500.00],

            // ---------------- Cardiology ----------------
            ['code' => 'CAR-001', 'name' => 'Electrocardiogram (ECG)',            'category' => 'cardiology', 'sample_type' => 'N/A',           'tat_hours' => 1,  'default_price' => 400.00],
            ['code' => 'CAR-002', 'name' => '2D Echocardiogram',                  'category' => 'cardiology', 'sample_type' => 'N/A',           'tat_hours' => 4,  'default_price' => 2500.00],
        ];

        foreach ($rows as $r) {
            LabTest::updateOrCreate(['code' => $r['code']], $r);
        }
    }
}