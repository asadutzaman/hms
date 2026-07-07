<?php

namespace App\Services\Billing;

use App\Enums\BillRefundStatusEnum;
use App\Enums\IpdBillStatusEnum;
use App\Enums\OpdBillStatusEnum;
use App\Exceptions\ApiException;
use App\Models\BillRefund;
use App\Models\IpdBill;
use App\Models\OpdBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefundService
{
    /**
     * Raise a refund request against an OPD or IPD bill — starts in
     * pending_approval; nothing is reversed on the bill until a supervisor
     * approves it via approve().
     */
    public function requestRefund(string $billableType, int $billableId, float $amount, string $reason, int $actorId): BillRefund
    {
        return DB::transaction(function () use ($billableType, $billableId, $amount, $reason, $actorId) {
            $bill = $this->findBill($billableType, $billableId);

            if ($amount <= 0 || $amount > (float) $bill->paid) {
                throw new ApiException('Refund amount must be greater than zero and cannot exceed the amount paid.', 422);
            }

            return BillRefund::query()->create([
                'organogram_id'  => $bill->organogram_id,
                'refund_no'      => $this->generateRefundNo(now()->toDateString()),
                'billable_type'  => $billableType,
                'billable_id'    => $billableId,
                'amount'         => round($amount, 2),
                'reason'         => $reason,
                'requested_by'   => $actorId,
                'requested_at'   => now(),
            ]);
        });
    }

    /**
     * Supervisor approval — approving IMMEDIATELY processes the refund
     * (reverses paid/balance on the bill and voids the receipt by flipping
     * the bill to 'refunded' once nothing remains paid). A separate manual
     * "processed" click-through wasn't worth the extra step for this sprint.
     */
    public function approve(int $refundId, int $actorId): BillRefund
    {
        return DB::transaction(function () use ($refundId, $actorId) {
            $refund = BillRefund::query()->lockForUpdate()->findOrFail($refundId);

            if ($refund->refund_status !== BillRefundStatusEnum::PENDING_APPROVAL) {
                throw new ApiException("Refund is already {$refund->refund_status}.", 422);
            }

            $bill = $this->findBill($refund->billable_type, $refund->billable_id);
            $bill->paid = max(0.0, round($bill->paid - $refund->amount, 2));
            $bill->balance = max(0.0, round($bill->total - $bill->paid, 2));

            if ($bill->paid <= 0.0) {
                $this->setBillStatus($bill, $refund->billable_type, 'refunded');
            }
            $bill->save();

            $refund->refund_status = BillRefundStatusEnum::PROCESSED;
            $refund->approved_by = $actorId;
            $refund->approved_at = now();
            $refund->save();

            return $refund;
        });
    }

    public function reject(int $refundId, int $actorId, string $reason): BillRefund
    {
        return DB::transaction(function () use ($refundId, $actorId, $reason) {
            $refund = BillRefund::query()->lockForUpdate()->findOrFail($refundId);

            if ($refund->refund_status !== BillRefundStatusEnum::PENDING_APPROVAL) {
                throw new ApiException("Refund is already {$refund->refund_status}.", 422);
            }

            $refund->refund_status = BillRefundStatusEnum::REJECTED;
            $refund->approved_by = $actorId;
            $refund->approved_at = now();
            $refund->notes = $reason;
            $refund->save();

            return $refund;
        });
    }

    protected function findBill(string $billableType, int $billableId)
    {
        return match ($billableType) {
            'opd_bill' => OpdBill::query()->lockForUpdate()->findOrFail($billableId),
            'ipd_bill' => IpdBill::query()->lockForUpdate()->findOrFail($billableId),
            default => throw new ApiException('Unknown billable type.', 422),
        };
    }

    protected function setBillStatus($bill, string $billableType, string $status): void
    {
        if ($billableType === 'opd_bill') {
            $bill->status = OpdBillStatusEnum::REFUNDED;
        } else {
            $bill->bill_status = IpdBillStatusEnum::REFUNDED;
        }
    }

    protected function generateRefundNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'BILL_REFUND')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'BILL_REFUND',
                    'prefix'        => 'REF',
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
                DB::table('code_sequences')
                    ->where('id', $row->id)
                    ->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "REF-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
