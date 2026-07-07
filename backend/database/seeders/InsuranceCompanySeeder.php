<?php

namespace Database\Seeders;

use App\Models\InsuranceCompany;
use App\Models\InsuranceScheme;
use Illuminate\Database\Seeder;

class InsuranceCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'code' => 'GRD-LIFE',
                'name' => 'Guardian Life Insurance',
                'tpa_type' => 'insurer',
                'contact_person' => 'Claims Desk',
                'phone' => '01700000001',
                'email' => 'claims@guardianlife.example',
                'credit_limit' => 500000.00,
                'schemes' => [
                    ['name' => 'Gold Health Plan', 'coverage_percent' => 90, 'max_limit' => 200000.00],
                    ['name' => 'Silver Health Plan', 'coverage_percent' => 70, 'max_limit' => 100000.00],
                ],
            ],
            [
                'code' => 'NAT-TPA',
                'name' => 'National TPA Services',
                'tpa_type' => 'corporate',
                'contact_person' => 'Corporate Desk',
                'phone' => '01700000002',
                'email' => 'corporate@nationaltpa.example',
                'credit_limit' => 1000000.00,
                'schemes' => [
                    ['name' => 'Corporate Employee Plan', 'coverage_percent' => 100, 'max_limit' => 300000.00],
                ],
            ],
        ];

        foreach ($companies as $c) {
            $schemes = $c['schemes'];
            unset($c['schemes']);

            $company = InsuranceCompany::updateOrCreate(['code' => $c['code']], $c);

            foreach ($schemes as $scheme) {
                InsuranceScheme::updateOrCreate(
                    ['insurance_company_id' => $company->id, 'name' => $scheme['name']],
                    $scheme + ['insurance_company_id' => $company->id]
                );
            }
        }
    }
}
