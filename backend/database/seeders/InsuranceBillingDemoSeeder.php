<?php

namespace Database\Seeders;

use App\Models\BillRefund;
use App\Models\InsuranceClaim;
use App\Models\InsuranceCompany;
use App\Models\OpdBill;
use App\Models\Patient;
use App\Models\PreAuthorization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Insurance + billing-adjacent volume: claims spread across the full status
 * lifecycle (draft -> submitted -> under_review -> approved/rejected ->
 * settled), a few pre-authorization requests, and a couple of bill refunds,
 * so the insurance tracking dashboard and claim/refund worklists render.
 *
 * Idempotent: claims/pre-auths/refunds keyed by their *_no columns.
 */
class InsuranceBillingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('[InsuranceBillingDemoSeeder] Starting ...');

        $patientIds = Patient::query()->pluck('id')->all();
        $companyIds = InsuranceCompany::query()->pluck('id')->all();
        $schemeIds  = DB::table('insurance_schemes')->pluck('id')->all();
        $billIds    = OpdBill::query()->pluck('id')->all();
        $actorId    = User::query()->where('email', 'doctor@hms.local')->value('id') ?? 1;

        if (empty($patientIds) || empty($companyIds) || empty($schemeIds)) {
            $this->command->warn('[InsuranceBillingDemoSeeder] Missing patients/insurance master data; skipping.');
            return;
        }

        $statuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'settled', 'settled'];
        $created = 0;

        foreach ($statuses as $i => $status) {
            $claimNo = 'CLM-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            if (InsuranceClaim::query()->where('claim_no', $claimNo)->exists()) {
                continue;
            }

            $amount = rand(2000, 15000);
            $billId = $billIds[$i % max(count($billIds), 1)] ?? null;

            InsuranceClaim::query()->forceCreate([
                'claim_no' => $claimNo,
                'patient_id' => $patientIds[$i % count($patientIds)],
                'insurance_company_id' => $companyIds[$i % count($companyIds)],
                'insurance_scheme_id' => $schemeIds[$i % count($schemeIds)],
                'policy_number' => 'POL-' . str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'billable_type' => OpdBill::class,
                'billable_id' => $billId,
                'claimed_amount' => $amount,
                'approved_amount' => in_array($status, ['approved', 'settled'], true) ? round($amount * 0.8, 2) : null,
                'claim_status' => $status,
                'submitted_by' => $actorId,
                'submitted_at' => $status === 'draft' ? null : Carbon::now()->subDays(20 - $i * 2),
                'settled_at' => $status === 'settled' ? Carbon::now()->subDays(2) : null,
                'created_by' => $actorId, 'updated_by' => $actorId, 'status' => 1,
            ]);
            $created++;
        }

        $this->command->info("[InsuranceBillingDemoSeeder] Insurance claims created: {$created}");

        $paStatuses = ['submitted', 'under_review', 'approved', 'approved', 'rejected'];
        $paCreated = 0;
        foreach ($paStatuses as $i => $status) {
            $paNo = 'PA-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            if (PreAuthorization::query()->where('pa_no', $paNo)->exists()) {
                continue;
            }

            $estimated = rand(5000, 30000);
            PreAuthorization::query()->forceCreate([
                'pa_no' => $paNo,
                'patient_id' => $patientIds[$i % count($patientIds)],
                'insurance_company_id' => $companyIds[$i % count($companyIds)],
                'insurance_scheme_id' => $schemeIds[$i % count($schemeIds)],
                'policy_number' => 'POL-' . str_pad((string) ($i + 10), 6, '0', STR_PAD_LEFT),
                'estimated_amount' => $estimated,
                'approved_amount' => $status === 'approved' ? round($estimated * 0.9, 2) : null,
                'diagnosis' => 'Planned in-patient procedure (demo)',
                'treatment_plan' => 'Standard treatment protocol',
                'pa_status' => $status,
                'requested_by' => $actorId, 'requested_at' => Carbon::now()->subDays(15 - $i * 2),
                'responded_at' => $status === 'submitted' ? null : Carbon::now()->subDays(10 - $i),
                'responded_by' => $status === 'submitted' ? null : $actorId,
                'created_by' => $actorId, 'updated_by' => $actorId, 'status' => 1,
            ]);
            $paCreated++;
        }

        $this->command->info("[InsuranceBillingDemoSeeder] Pre-authorizations created: {$paCreated}");

        $refundStatuses = ['pending_approval', 'approved', 'processed'];
        $refundCreated = 0;
        foreach ($refundStatuses as $i => $status) {
            $refundNo = 'RFD-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            if (BillRefund::query()->where('refund_no', $refundNo)->exists()) {
                continue;
            }

            $billId = $billIds[$i % max(count($billIds), 1)] ?? null;
            BillRefund::query()->forceCreate([
                'refund_no' => $refundNo,
                'billable_type' => OpdBill::class,
                'billable_id' => $billId,
                'amount' => rand(200, 1500),
                'reason' => 'Overpayment refund (demo)',
                'payment_method_reversed' => 'cash',
                'refund_status' => $status,
                'requested_by' => $actorId, 'requested_at' => Carbon::now()->subDays(10 - $i),
                'approved_by' => $status === 'pending_approval' ? null : $actorId,
                'approved_at' => $status === 'pending_approval' ? null : Carbon::now()->subDays(5 - $i),
                'created_by' => $actorId, 'updated_by' => $actorId, 'status' => 1,
            ]);
            $refundCreated++;
        }

        $this->command->info("[InsuranceBillingDemoSeeder] Bill refunds created: {$refundCreated}");
        $this->command->info('[InsuranceBillingDemoSeeder] Done.');
    }
}
