<?php

namespace App\Services\Insurance;

use App\Enums\InsuranceClaimStatusEnum;
use App\Enums\IpdBillItemTypeEnum;
use App\Enums\OpdBillItemTypeEnum;
use App\Exceptions\ApiException;
use App\Models\InsuranceClaim;
use App\Models\InsuranceClaimSettlement;
use App\Models\IpdBill;
use App\Models\IpdBillItem;
use App\Models\OpdBill;
use App\Models\OpdBillItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * F-20-05 Settlement & Reconciliation — matches an insurer's bank receipt
 * against a claim and, if the settled amount falls short of what was
 * claimed/approved, bills the patient for the difference by adding an
 * 'insurance_adjustment' line to the original bill (same forceCreate +
 * manual total-recompute pattern as PackageBillingService — the OpdBillItem
 * model's $fillable doesn't match its real migrated columns, see
 * project_hms_sprint7_scope memory).
 */
class InsuranceClaimSettlementService
{
    public function settle(int $claimId, array $data, int $actorId): InsuranceClaimSettlement
    {
        return DB::transaction(function () use ($claimId, $data, $actorId) {
            $claim = InsuranceClaim::query()->lockForUpdate()->findOrFail($claimId);

            if (!InsuranceClaimStatusEnum::canTransition($claim->claim_status, InsuranceClaimStatusEnum::SETTLED)) {
                throw new ApiException("Cannot settle a claim in status '{$claim->claim_status}'.", 422);
            }

            $basis = (float) ($claim->approved_amount ?? $claim->claimed_amount);
            $settledAmount = round((float) $data['settled_amount'], 2);
            $shortfall = max(0.0, round($basis - $settledAmount, 2));

            $settlement = InsuranceClaimSettlement::query()->create([
                'organogram_id'      => $claim->organogram_id,
                'settlement_no'      => $this->generateSettlementNo(now()->toDateString()),
                'insurance_claim_id' => $claim->id,
                'bank_reference_no'  => $data['bank_reference_no'],
                'bank_receipt_date'  => $data['bank_receipt_date'],
                'settled_amount'     => $settledAmount,
                'shortfall_amount'   => $shortfall,
                'notes'              => $data['notes'] ?? null,
                'settled_by'         => $actorId,
                'created_by'         => $actorId,
            ]);

            if ($shortfall > 0) {
                $this->billShortfallToPatient($claim, $shortfall);
                $settlement->patient_billed = true;
                $settlement->save();
            }

            $claim->claim_status = InsuranceClaimStatusEnum::SETTLED;
            $claim->settled_at = now();
            $claim->save();

            return $settlement->fresh('claim');
        });
    }

    protected function billShortfallToPatient(InsuranceClaim $claim, float $shortfall): void
    {
        $description = "Insurance shortfall — Claim {$claim->claim_no}";

        if ($claim->billable_type === 'opd_bill') {
            $bill = OpdBill::query()->lockForUpdate()->findOrFail($claim->billable_id);
            $nextSequence = (int) OpdBillItem::query()->where('opd_bill_id', $bill->id)->max('sequence') + 1;

            OpdBillItem::query()->forceCreate([
                'organogram_id' => $bill->organogram_id,
                'opd_bill_id'   => $bill->id,
                'item_type'     => OpdBillItemTypeEnum::INSURANCE_ADJUSTMENT,
                'description'   => $description,
                'source_type'   => InsuranceClaim::class,
                'source_id'     => $claim->id,
                'quantity'      => 1,
                'unit_price'    => $shortfall,
                'line_total'    => $shortfall,
                'sequence'      => $nextSequence,
            ]);

            $bill->subtotal = round($bill->subtotal + $shortfall, 2);
            $bill->total = max(0.0, round($bill->subtotal - $bill->discount + $bill->tax, 2));
            $bill->balance = max(0.0, round($bill->total - $bill->paid, 2));
            $bill->save();
        } elseif ($claim->billable_type === 'ipd_bill') {
            $bill = IpdBill::query()->lockForUpdate()->findOrFail($claim->billable_id);
            $nextSequence = (int) IpdBillItem::query()->where('ipd_bill_id', $bill->id)->max('sequence') + 1;

            IpdBillItem::query()->create([
                'organogram_id' => $bill->organogram_id,
                'ipd_bill_id'   => $bill->id,
                'item_type'     => IpdBillItemTypeEnum::INSURANCE_ADJUSTMENT,
                'description'   => $description,
                'source_type'   => InsuranceClaim::class,
                'source_id'     => $claim->id,
                'quantity'      => 1,
                'unit_price'    => $shortfall,
                'line_total'    => $shortfall,
                'sequence'      => $nextSequence,
            ]);

            $bill->subtotal = round($bill->subtotal + $shortfall, 2);
            $bill->total = max(0.0, round($bill->subtotal - $bill->discount + $bill->tax, 2));
            $bill->balance = max(0.0, round($bill->total - $bill->paid, 2));
            $bill->save();
        } else {
            throw new ApiException('Unknown billable type on claim.', 422);
        }
    }

    protected function generateSettlementNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'CLAIM_SETTLEMENT')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'CLAIM_SETTLEMENT',
                    'prefix'        => 'STL',
                    'separator'     => '-',
                    'next_sequence' => 2,
                    'sequence_date' => $dateYmd,
                    'status'        => 1,
                    'sort_order'    => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')->where('id', $row->id)->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "STL-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
