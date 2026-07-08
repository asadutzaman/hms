<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AuthSeeder::class,
            LabTestSeeder::class,
            LabTestParameterSeeder::class,
            NotificationTemplateSeeder::class,
            RadiologyTestSeeder::class,
            InsuranceCompanySeeder::class,
            OpdDemoSeeder::class,
            MasterDataDemoSeeder::class,
            PatientHrDemoSeeder::class,
            AppointmentOpdDemoSeeder::class,
            IpdErDemoSeeder::class,
            LabRadiologyDemoSeeder::class,
            InventoryDemoSeeder::class,
            InsuranceBillingDemoSeeder::class,
            BloodBankOtDemoSeeder::class,
            PaymentNotificationDemoSeeder::class,
        ]);
    }
}
