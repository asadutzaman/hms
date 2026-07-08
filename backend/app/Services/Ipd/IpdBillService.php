<?php

namespace App\Services\Ipd;

use App\Enums\IpdAdmissionActionEnum;
use App\Enums\IpdAdvancePaymentStatusEnum;
use App\Enums\IpdBillItemTypeEnum;
use App\Enums\IpdBillStatusEnum;
use App\Enums\IpdDiscountStatusEnum;
use App\Enums\IpdPaymentMethodEnum;
use App\Exceptions\ApiException;
use App\Models\Bed;
use App\Models\IpdAdmission;
use App\Models\IpdAdmissionAuditLog;
use App\Models\IpdAdvancePayment;
use App\Models\IpdBill;
use App\Models\IpdBillItem;
use App\Models\IpdBillPayment;
use App\Repositories\IpdAdvancePaymentRepository;
use App\Repositories\IpdBillItemRepository;
use App\Repositories\IpdBillPaymentRepository;
use App\Repositories\IpdBillRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IpdBillService
{
    /**
     * Discounts within this percentage of the bill subtotal are auto-approved;
     * anything above is held as pending_discount until a supervisor approves it
     * (mirrors OpdBillService's threshold for consistency across OPD/IPD).
     */
    public const DISCOUNT_APPROVAL_THRESHOLD_PERCENT = 10.0;

    protected IpdBillRepository $billRepository;
    protected IpdBillItemRepository $itemRepository;
    protected IpdBillPaymentRepository $paymentRepository;
    protected IpdAdvancePaymentRepository $advanceRepository;

    public function __construct(
        IpdBillRepository $billRepository,
        IpdBillItemRepository $itemRepository,
        IpdBillPaymentRepository $paymentRepository,
        IpdAdvancePaymentRepository $advanceRepository,
    ) {
        $this->billRepository    = $billRepository;
        $this->itemRepository    = $itemRepository;
        $this->paymentRepository = $paymentRepository;
        $this->advanceRepository = $advanceRepository;
    }

    /* ---------- Bill shell ---------- */

    /**
     * Create the (empty) bill shell for an admission. Idempotent — returns the
     * existing bill if one is already there. Called automatically on admit();
     * exposed as an endpoint too in case an older admission predates this hook.
     */
    public function generate(int $admissionId, int $actorId): IpdBill
    {
        return DB::transaction(function () use ($admissionId, $actorId) {
            $existing = $this->billRepository->forAdmission($admissionId);
            if ($existing) {
                return $existing;
            }

            $admission = IpdAdmission::query()->lockForUpdate()->findOrFail($admissionId);
            $dateYmd = Carbon::parse($admission->admission_date)->toDateString();

            $bill = $this->billRepository->create([
                'organogram_id' => $admission->organogram_id,
                'admission_id'  => $admission->id,
                'bill_no'       => $this->generateBillNo($dateYmd),
                'billed_by'     => $actorId,
                'billed_at'     => now(),
            ]);

            return $bill->fresh(['items', 'payments']);
        });
    }

    public function findForAdmission(int $admissionId): ?IpdBill
    {
        return $this->billRepository->forAdmission($admissionId);
    }

    /* ---------- Room charges (bed-occupancy segments) ---------- */

    /**
     * Recompute the room_charge line items for a bill from the admission's
     * audit-log history (admit / bed_transfer / exit events), each segment
     * priced at the bed's daily_rate captured in that event's payload.
     * Replaces (does not append to) existing room_charge items — safe to call
     * repeatedly, e.g. every time the billing tab is opened or before discharge.
     */
    public function refreshRoomCharges(int $admissionId, int $actorId): IpdBill
    {
        return DB::transaction(function () use ($admissionId, $actorId) {
            $admission = IpdAdmission::query()->findOrFail($admissionId);
            $bill = $this->billRepository->forAdmission($admissionId);
            if (!$bill) {
                $bill = $this->generate($admissionId, $actorId);
            }

            $segments = $this->computeRoomChargeSegments($admission);

            $this->itemRepository->deleteByType($bill->id, IpdBillItemTypeEnum::ROOM_CHARGE);

            $sequence = 1;
            foreach ($segments as $segment) {
                IpdBillItem::query()->create([
                    'organogram_id' => $admission->organogram_id,
                    'ipd_bill_id'   => $bill->id,
                    'item_type'     => IpdBillItemTypeEnum::ROOM_CHARGE,
                    'description'   => $segment['description'],
                    'quantity'      => $segment['nights'],
                    'unit_price'    => $segment['daily_rate'],
                    'line_total'    => $segment['amount'],
                    'source_type'   => Bed::class,
                    'source_id'     => $segment['bed_id'],
                    'sequence'      => $sequence++,
                ]);
            }

            return $this->recomputeTotals($bill->id);
        });
    }

    protected function computeRoomChargeSegments(IpdAdmission $admission): array
    {
        $logs = IpdAdmissionAuditLog::query()
            ->where('ipd_admission_id', $admission->id)
            ->whereIn('action', [
                IpdAdmissionActionEnum::ADMIT,
                IpdAdmissionActionEnum::BED_TRANSFER,
                IpdAdmissionActionEnum::DISCHARGE,
                IpdAdmissionActionEnum::DISCHARGE_OVERRIDE,
                IpdAdmissionActionEnum::DAMA,
                IpdAdmissionActionEnum::DECEASED,
            ])
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        $segments = [];
        $current = null;

        foreach ($logs as $log) {
            $payload = $log->payload ?? [];

            if ($log->action === IpdAdmissionActionEnum::ADMIT) {
                // Use the admission's own admission_date, not the audit log's
                // occurred_at — the log is always stamped "now" even when the
                // admission itself is recorded/backdated to an earlier time.
                $current = [
                    'bed_id'     => $payload['bed_id'] ?? $admission->bed_id,
                    'ward_id'    => $payload['ward_id'] ?? $admission->ward_id,
                    'daily_rate' => (float) ($payload['daily_rate'] ?? 0),
                    'from'       => $admission->admission_date ?? $log->occurred_at,
                ];
                continue;
            }

            if ($log->action === IpdAdmissionActionEnum::BED_TRANSFER && $current) {
                $segments[] = $this->closeSegment($current, $log->occurred_at);
                $current = [
                    'bed_id'     => $payload['to_bed_id'] ?? $admission->bed_id,
                    'ward_id'    => $payload['to_ward_id'] ?? $admission->ward_id,
                    'daily_rate' => (float) ($payload['daily_rate'] ?? 0),
                    'from'       => $log->occurred_at,
                ];
                continue;
            }

            // Terminal exit events close the final segment.
            if ($current) {
                $segments[] = $this->closeSegment($current, $log->occurred_at);
                $current = null;
            }
        }

        // Still admitted — final segment runs through now().
        if ($current) {
            $segments[] = $this->closeSegment($current, now());
        }

        $beds = Bed::query()->with('ward')->whereIn('id', array_column($segments, 'bed_id'))->get()->keyBy('id');

        return array_map(function ($segment) use ($beds) {
            $bed = $beds->get($segment['bed_id']);
            $wardName = optional(optional($bed)->ward)->name ?? 'Ward';
            $bedNumber = optional($bed)->bed_number ?? $segment['bed_id'];

            $segment['description'] = sprintf(
                'Room charge — %s / Bed %s (%s to %s, %d night%s)',
                $wardName,
                $bedNumber,
                Carbon::parse($segment['from'])->format('Y-m-d'),
                Carbon::parse($segment['to'])->format('Y-m-d'),
                $segment['nights'],
                $segment['nights'] > 1 ? 's' : '',
            );

            return $segment;
        }, $segments);
    }

    protected function closeSegment(array $segment, $to): array
    {
        $from = Carbon::parse($segment['from']);
        $toCarbon = Carbon::parse($to);
        $hours = max(0, $from->diffInHours($toCarbon));
        $nights = max(1, (int) ceil($hours / 24));

        $segment['to']     = $toCarbon;
        $segment['nights'] = $nights;
        $segment['amount'] = round($nights * $segment['daily_rate'], 2);

        return $segment;
    }

    /* ---------- Manual line items ---------- */

    public function addManualItem(int $billId, array $data, int $actorId): IpdBill
    {
        return DB::transaction(function () use ($billId, $data, $actorId) {
            $bill = $this->billRepository->show($billId);
            $this->assertNotFinalized($bill);

            $quantity = (float) ($data['quantity'] ?? 1);
            $unitPrice = (float) ($data['unit_price'] ?? 0);
            $lastSequence = (int) $this->itemRepository->forBill($bill->id)->max('sequence');

            IpdBillItem::query()->create([
                'organogram_id' => $bill->organogram_id,
                'ipd_bill_id'   => $bill->id,
                'item_type'     => $data['item_type'] ?? IpdBillItemTypeEnum::OTHER,
                'description'   => $data['description'],
                'quantity'      => $quantity ?: 1,
                'unit_price'    => $unitPrice,
                'line_total'    => round(($quantity ?: 1) * $unitPrice, 2),
                'sequence'      => $lastSequence + 1,
            ]);

            return $this->recomputeTotals($bill->id);
        });
    }

    public function removeItem(int $billId, int $itemId): IpdBill
    {
        return DB::transaction(function () use ($billId, $itemId) {
            $bill = $this->billRepository->show($billId);
            $this->assertNotFinalized($bill);

            IpdBillItem::query()->where('ipd_bill_id', $billId)->where('id', $itemId)->delete();

            return $this->recomputeTotals($bill->id);
        });
    }

    /* ---------- Payments ---------- */

    public function recordPayment(int $billId, array $data, ?int $actorId): IpdBill
    {
        return DB::transaction(function () use ($billId, $data, $actorId) {
            $bill = $this->billRepository->show($billId);

            if ($bill->bill_status === IpdBillStatusEnum::PAID) {
                throw new ApiException("Bill {$bill->bill_no} is already fully paid.", 422);
            }

            $amount = (float) ($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new ApiException('Payment amount must be greater than zero.', 422);
            }

            IpdBillPayment::query()->create([
                'organogram_id'  => $bill->organogram_id,
                'ipd_bill_id'    => $bill->id,
                'amount'         => round($amount, 2),
                'payment_method' => $data['payment_method'] ?? IpdPaymentMethodEnum::CASH,
                'reference_no'   => $data['reference_no'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'paid_by'        => $actorId,
                'paid_at'        => $data['paid_at'] ?? now(),
            ]);

            $this->logAdmissionAudit(
                $bill->admission_id,
                IpdAdmissionActionEnum::PAYMENT_RECORDED,
                $actorId,
                "Payment of {$amount} via " . ($data['payment_method'] ?? IpdPaymentMethodEnum::CASH),
                ['bill_id' => $bill->id, 'amount' => $amount],
            );

            return $this->recomputeTotals($bill->id);
        });
    }

    public function recordSplitPayment(int $billId, array $payments, int $actorId): IpdBill
    {
        return DB::transaction(function () use ($billId, $payments, $actorId) {
            foreach ($payments as $payment) {
                $this->recordPayment($billId, $payment, $actorId);
            }
            return $this->billRepository->withItemsAndPayments($billId);
        });
    }

    /* ---------- Advance payments ---------- */

    public function receiveAdvance(int $admissionId, array $data, int $actorId): IpdAdvancePayment
    {
        return DB::transaction(function () use ($admissionId, $data, $actorId) {
            $admission = IpdAdmission::query()->findOrFail($admissionId);

            $advance = IpdAdvancePayment::query()->create([
                'organogram_id'  => $admission->organogram_id,
                'admission_id'   => $admissionId,
                'amount'         => round((float) $data['amount'], 2),
                'payment_method' => $data['payment_method'] ?? IpdPaymentMethodEnum::CASH,
                'reference_no'   => $data['reference_no'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'received_by'    => $actorId,
                'received_at'    => $data['received_at'] ?? now(),
            ]);

            $this->logAdmissionAudit(
                $admissionId,
                IpdAdmissionActionEnum::ADVANCE_RECEIVED,
                $actorId,
                "Advance of {$advance->amount} received",
                ['advance_id' => $advance->id, 'amount' => $advance->amount],
            );

            return $advance->fresh();
        });
    }

    /**
     * Apply part or all of a single advance-payment's unapplied balance to the
     * admission's bill as a payment. Caller must generate the bill first.
     */
    public function applyAdvanceToBill(int $advanceId, ?float $amount, int $actorId): IpdBill
    {
        return DB::transaction(function () use ($advanceId, $amount, $actorId) {
            $advance = IpdAdvancePayment::query()->lockForUpdate()->findOrFail($advanceId);
            $unapplied = round((float) $advance->amount - (float) $advance->applied_amount, 2);

            if ($unapplied <= 0) {
                throw new ApiException('This advance has already been fully applied.', 422);
            }

            $applyAmount = $amount !== null ? round($amount, 2) : $unapplied;
            if ($applyAmount <= 0 || $applyAmount > $unapplied) {
                throw new ApiException("Amount must be between 0.01 and {$unapplied}.", 422);
            }

            $bill = $this->billRepository->forAdmission($advance->admission_id);
            if (!$bill) {
                throw new ApiException('No bill exists for this admission yet.', 422);
            }

            $advance->applied_amount = round((float) $advance->applied_amount + $applyAmount, 2);
            $advance->advance_status = $advance->applied_amount >= $advance->amount
                ? IpdAdvancePaymentStatusEnum::FULLY_APPLIED
                : IpdAdvancePaymentStatusEnum::PARTIALLY_APPLIED;
            $advance->save();

            IpdBillPayment::query()->create([
                'organogram_id'  => $bill->organogram_id,
                'ipd_bill_id'    => $bill->id,
                'amount'         => $applyAmount,
                'payment_method' => IpdPaymentMethodEnum::ADVANCE,
                'reference_no'   => 'ADV-' . $advance->id,
                'notes'          => 'Applied from advance payment #' . $advance->id,
                'paid_by'        => $actorId,
                'paid_at'        => now(),
            ]);

            $this->logAdmissionAudit(
                $advance->admission_id,
                IpdAdmissionActionEnum::ADVANCE_APPLIED,
                $actorId,
                "Advance of {$applyAmount} applied to bill {$bill->bill_no}",
                ['advance_id' => $advance->id, 'amount' => $applyAmount],
            );

            return $this->recomputeTotals($bill->id);
        });
    }

    /* ---------- Discount workflow ---------- */

    public function applyDiscount(int $billId, float $amount, string $type, ?string $reason, int $actorId): IpdBill
    {
        return DB::transaction(function () use ($billId, $amount, $type, $reason, $actorId) {
            $bill = $this->billRepository->show($billId);
            $this->assertNotFinalized($bill);

            $discountAmount = $type === 'percent'
                ? round(((float) $bill->subtotal) * $amount / 100, 2)
                : round($amount, 2);

            $thresholdAmount = round(((float) $bill->subtotal) * self::DISCOUNT_APPROVAL_THRESHOLD_PERCENT / 100, 2);
            $autoApprove = $discountAmount <= $thresholdAmount;

            $bill->discount_reason = $reason;
            $bill->discount_type = $type;

            if ($autoApprove) {
                $bill->discount = round((float) $bill->discount + $discountAmount, 2);
                $bill->discount_status = IpdDiscountStatusEnum::APPROVED;
                $bill->discount_approved_by = $actorId;
                $bill->discount_approved_at = now();
                $bill->pending_discount = 0;
            } else {
                $bill->pending_discount = $discountAmount;
                $bill->discount_status = IpdDiscountStatusEnum::PENDING_APPROVAL;
            }

            $bill->save();

            $this->logAdmissionAudit(
                $bill->admission_id,
                $autoApprove ? IpdAdmissionActionEnum::DISCOUNT_APPROVED : IpdAdmissionActionEnum::DISCOUNT_REQUESTED,
                $actorId,
                "Discount of {$discountAmount} " . ($autoApprove ? 'auto-approved' : 'requested for approval'),
                ['bill_id' => $bill->id, 'amount' => $discountAmount],
            );

            return $this->recomputeTotals($bill->id);
        });
    }

    public function approveDiscount(int $billId, int $actorId): IpdBill
    {
        return DB::transaction(function () use ($billId, $actorId) {
            $bill = $this->billRepository->show($billId);

            if ($bill->discount_status !== IpdDiscountStatusEnum::PENDING_APPROVAL) {
                throw new ApiException('No pending discount to approve on this bill.', 422);
            }

            $bill->discount = round((float) $bill->discount + (float) $bill->pending_discount, 2);
            $bill->pending_discount = 0;
            $bill->discount_status = IpdDiscountStatusEnum::APPROVED;
            $bill->discount_approved_by = $actorId;
            $bill->discount_approved_at = now();
            $bill->save();

            $this->logAdmissionAudit(
                $bill->admission_id,
                IpdAdmissionActionEnum::DISCOUNT_APPROVED,
                $actorId,
                "Discount of {$bill->discount} approved",
                ['bill_id' => $bill->id],
            );

            return $this->recomputeTotals($bill->id);
        });
    }

    public function rejectDiscount(int $billId, int $actorId, string $reason): IpdBill
    {
        return DB::transaction(function () use ($billId, $actorId, $reason) {
            $bill = $this->billRepository->show($billId);

            if ($bill->discount_status !== IpdDiscountStatusEnum::PENDING_APPROVAL) {
                throw new ApiException('No pending discount to reject on this bill.', 422);
            }

            $bill->pending_discount = 0;
            $bill->discount_status = IpdDiscountStatusEnum::REJECTED;
            $bill->save();

            $this->logAdmissionAudit(
                $bill->admission_id,
                IpdAdmissionActionEnum::DISCOUNT_REJECTED,
                $actorId,
                "Discount rejected: {$reason}",
                ['bill_id' => $bill->id],
            );

            return $this->recomputeTotals($bill->id);
        });
    }

    /* ---------- Waive ---------- */

    public function waive(int $billId, int $actorId, string $reason): IpdBill
    {
        return DB::transaction(function () use ($billId, $actorId, $reason) {
            $bill = $this->billRepository->show($billId);

            if ($bill->bill_status === IpdBillStatusEnum::PAID) {
                throw new ApiException('Cannot waive a fully paid bill.', 422);
            }

            $bill->bill_status = IpdBillStatusEnum::WAIVED;
            $bill->is_finalized = true;
            $bill->balance = 0;
            $bill->save();

            $this->logAdmissionAudit(
                $bill->admission_id,
                IpdAdmissionActionEnum::UPDATE,
                $actorId,
                "Bill waived: {$reason}",
                ['bill_id' => $bill->id, 'reason' => $reason],
            );

            return $bill->fresh(['items', 'payments']);
        });
    }

    /* ---------- Clearance check (used by the admission discharge gate) ---------- */

    public function isCleared(int $admissionId): bool
    {
        $bill = $this->billRepository->forAdmission($admissionId);
        if (!$bill) {
            return true;
        }

        return $bill->balance <= 0 || in_array($bill->bill_status, [IpdBillStatusEnum::PAID, IpdBillStatusEnum::WAIVED], true);
    }

    /* ---------- Receipt PDF (F-17-04 — IPD had no PDF generation at all before Sprint 8) ---------- */

    public function renderReceiptPdf(int $billId)
    {
        $bill = $this->billRepository->newQuery()
            ->with(['items', 'payments', 'admission.patient', 'admission.ward', 'admission.bed', 'admission.attendingDoctor'])
            ->find($billId);

        if (!$bill) {
            throw new ApiException('Bill not found.', 404);
        }

        $pdf = Pdf::loadView('pdf.ipd_receipt', ['bill' => $bill]);

        return $pdf->stream("receipt-{$bill->bill_no}.pdf");
    }

    /* ---------- Totals ---------- */

    protected function recomputeTotals(int $billId): IpdBill
    {
        $bill = $this->billRepository->withItemsAndPayments($billId);

        $subtotal = round((float) $bill->items->sum('line_total'), 2);
        $paid = round((float) $this->paymentRepository->sumForBill($billId), 2);
        $total = max(0.0, round($subtotal - (float) $bill->discount + (float) $bill->tax, 2));
        $balance = max(0.0, round($total - $paid, 2));

        $status = $bill->bill_status;
        if ($status !== IpdBillStatusEnum::WAIVED) {
            if ($total > 0 && $paid >= $total) {
                $status = IpdBillStatusEnum::PAID;
            } elseif ($paid > 0) {
                $status = IpdBillStatusEnum::PARTIAL;
            } else {
                $status = IpdBillStatusEnum::UNPAID;
            }
        }

        $bill->subtotal = $subtotal;
        $bill->total = $total;
        $bill->paid = $paid;
        $bill->balance = $balance;
        $bill->bill_status = $status;
        $bill->is_finalized = $status === IpdBillStatusEnum::PAID || $status === IpdBillStatusEnum::WAIVED;
        $bill->save();

        return $bill->fresh(['items', 'payments']);
    }

    protected function assertNotFinalized(IpdBill $bill): void
    {
        if ($bill->is_finalized) {
            throw new ApiException("Bill {$bill->bill_no} is finalized and can no longer be modified.", 422);
        }
    }

    /* ---------- Helpers ---------- */

    protected function logAdmissionAudit(int $admissionId, string $action, int $actorId, ?string $remarks, array $meta = []): void
    {
        $admission = IpdAdmission::query()->find($admissionId);
        if (!$admission) {
            return;
        }

        IpdAdmissionAuditLog::query()->create([
            'organogram_id'    => $admission->organogram_id,
            'ipd_admission_id' => $admissionId,
            'actor_id'         => $actorId,
            'action'           => $action,
            'remarks'          => $remarks,
            'payload'          => $meta,
            'occurred_at'      => now(),
        ]);
    }

    protected function generateBillNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'IPD_BILL')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'IPD_BILL',
                    'prefix'        => 'IPDBILL',
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
                    ->update([
                        'next_sequence' => $seq + 1,
                        'updated_at'    => now(),
                    ]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "IPDBILL-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
