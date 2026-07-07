<?php

namespace Database\Seeders;

use App\Models\LabTest;
use App\Models\LabTestParameter;
use App\Models\LabTestReferenceRange;
use Illuminate\Database\Seeder;

class LabTestParameterSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'HEM-001' => [ // Complete Blood Count
                ['name' => 'Haemoglobin', 'unit' => 'g/dL', 'ranges' => [
                    ['gender' => 'male', 'low' => 13.5, 'high' => 17.5],
                    ['gender' => 'female', 'low' => 12.0, 'high' => 15.5],
                ]],
                ['name' => 'WBC Count', 'unit' => '10^3/uL', 'ranges' => [
                    ['gender' => 'all', 'low' => 4.0, 'high' => 11.0],
                ]],
                ['name' => 'Platelet Count', 'unit' => '10^3/uL', 'critical_low' => 20, 'critical_high' => 1000, 'ranges' => [
                    ['gender' => 'all', 'low' => 150, 'high' => 450],
                ]],
            ],
            'HEM-003' => [ // Haemoglobin (standalone)
                ['name' => 'Haemoglobin', 'unit' => 'g/dL', 'critical_low' => 6.5, 'ranges' => [
                    ['gender' => 'male', 'low' => 13.5, 'high' => 17.5],
                    ['gender' => 'female', 'low' => 12.0, 'high' => 15.5],
                ]],
            ],
            'HEM-004' => [ // Platelet Count (standalone)
                ['name' => 'Platelet Count', 'unit' => '10^3/uL', 'critical_low' => 20, 'critical_high' => 1000, 'ranges' => [
                    ['gender' => 'all', 'low' => 150, 'high' => 450],
                ]],
            ],
            'BIO-001' => [ // Fasting Blood Sugar
                ['name' => 'Fasting Blood Sugar', 'unit' => 'mg/dL', 'critical_low' => 40, 'critical_high' => 500, 'ranges' => [
                    ['gender' => 'all', 'low' => 70, 'high' => 100],
                ]],
            ],
            'BIO-002' => [ // Post-Prandial Blood Sugar
                ['name' => 'PP Blood Sugar', 'unit' => 'mg/dL', 'critical_high' => 500, 'ranges' => [
                    ['gender' => 'all', 'low' => 70, 'high' => 140],
                ]],
            ],
            'BIO-003' => [ // HbA1c
                ['name' => 'HbA1c', 'unit' => '%', 'ranges' => [
                    ['gender' => 'all', 'low' => 4.0, 'high' => 5.6],
                ]],
            ],
            'BIO-004' => [ // Lipid Profile
                ['name' => 'Total Cholesterol', 'unit' => 'mg/dL', 'ranges' => [
                    ['gender' => 'all', 'low' => 0, 'high' => 200],
                ]],
                ['name' => 'Triglycerides', 'unit' => 'mg/dL', 'ranges' => [
                    ['gender' => 'all', 'low' => 0, 'high' => 150],
                ]],
                ['name' => 'HDL Cholesterol', 'unit' => 'mg/dL', 'ranges' => [
                    ['gender' => 'all', 'low' => 40, 'high' => 60],
                ]],
                ['name' => 'LDL Cholesterol', 'unit' => 'mg/dL', 'ranges' => [
                    ['gender' => 'all', 'low' => 0, 'high' => 100],
                ]],
            ],
            'BIO-005' => [ // Liver Function Test
                ['name' => 'SGPT (ALT)', 'unit' => 'U/L', 'ranges' => [
                    ['gender' => 'all', 'low' => 7, 'high' => 56],
                ]],
                ['name' => 'SGOT (AST)', 'unit' => 'U/L', 'ranges' => [
                    ['gender' => 'all', 'low' => 5, 'high' => 40],
                ]],
                ['name' => 'Total Bilirubin', 'unit' => 'mg/dL', 'critical_high' => 15, 'ranges' => [
                    ['gender' => 'all', 'low' => 0.1, 'high' => 1.2],
                ]],
                ['name' => 'Serum Albumin', 'unit' => 'g/dL', 'ranges' => [
                    ['gender' => 'all', 'low' => 3.5, 'high' => 5.0],
                ]],
            ],
            'BIO-006' => [ // Renal Function Test
                ['name' => 'Blood Urea', 'unit' => 'mg/dL', 'critical_high' => 100, 'ranges' => [
                    ['gender' => 'all', 'low' => 7, 'high' => 20],
                ]],
                ['name' => 'Serum Creatinine', 'unit' => 'mg/dL', 'critical_high' => 7, 'ranges' => [
                    ['gender' => 'male', 'low' => 0.7, 'high' => 1.3],
                    ['gender' => 'female', 'low' => 0.6, 'high' => 1.1],
                ]],
                ['name' => 'Uric Acid', 'unit' => 'mg/dL', 'ranges' => [
                    ['gender' => 'male', 'low' => 3.4, 'high' => 7.0],
                    ['gender' => 'female', 'low' => 2.4, 'high' => 6.0],
                ]],
            ],
            'BIO-007' => [ // Serum Electrolytes
                ['name' => 'Sodium', 'unit' => 'mmol/L', 'critical_low' => 120, 'critical_high' => 160, 'ranges' => [
                    ['gender' => 'all', 'low' => 135, 'high' => 145],
                ]],
                ['name' => 'Potassium', 'unit' => 'mmol/L', 'critical_low' => 2.5, 'critical_high' => 6.5, 'ranges' => [
                    ['gender' => 'all', 'low' => 3.5, 'high' => 5.1],
                ]],
                ['name' => 'Chloride', 'unit' => 'mmol/L', 'ranges' => [
                    ['gender' => 'all', 'low' => 98, 'high' => 107],
                ]],
            ],
            'BIO-009' => [ // TSH
                ['name' => 'TSH', 'unit' => 'uIU/mL', 'ranges' => [
                    ['gender' => 'all', 'low' => 0.4, 'high' => 4.0],
                ]],
            ],
            'SER-003' => [ // CRP
                ['name' => 'CRP', 'unit' => 'mg/L', 'ranges' => [
                    ['gender' => 'all', 'low' => 0, 'high' => 10],
                ]],
            ],
        ];

        foreach ($catalog as $code => $parameters) {
            $labTest = LabTest::query()->where('code', $code)->first();
            if (!$labTest) {
                continue;
            }

            foreach ($parameters as $sequence => $paramData) {
                $parameter = LabTestParameter::updateOrCreate(
                    ['lab_test_id' => $labTest->id, 'parameter_name' => $paramData['name']],
                    [
                        'unit' => $paramData['unit'],
                        'result_data_type' => 'numeric',
                        'critical_low' => $paramData['critical_low'] ?? null,
                        'critical_high' => $paramData['critical_high'] ?? null,
                        'sequence' => $sequence + 1,
                    ]
                );

                foreach ($paramData['ranges'] as $range) {
                    LabTestReferenceRange::updateOrCreate(
                        ['lab_test_parameter_id' => $parameter->id, 'gender' => $range['gender']],
                        [
                            'age_min_years' => 0,
                            'age_max_years' => null,
                            'range_low' => $range['low'],
                            'range_high' => $range['high'],
                        ]
                    );
                }
            }
        }
    }
}
