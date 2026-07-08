<?php

namespace App\Services\Opd;

use App\Enums\OpdBillStatusEnum;
use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Exceptions\ApiException;
use App\Models\OpdBill;
use App\Models\OpdVisit;
use App\Repositories\OpdBillPaymentRepository;
use App\Repositories\OpdBillRepository;
use App\Repositories\OpdVisitRepository;
use App\Validators\OpdBillPaymentValidator;
use App\Validators\OpdBillValidator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpdBillService
{
    /**
     * Discounts above this percentage of the bill subtotal require supervisor
     * approval instead of being applied immediately.
     */
    protected const DISCOUNT_APPROVAL_THRESHOLD_PERCENT = 10.0;

    protected $billRepo;
    protected $paymentRepo;
    protected $visitRepo;

    public function __construct(
        OpdBillRepository $billRepo,
        OpdBillPaymentRepository $paymentRepo,
        OpdVisitRepository $visitRepo,
    ) {
        $this->billRepo   = $billRepo;
        $this->paymentRepo= $paymentRepo;
        $this->visitRepo  = $visitRepo;
    }

    /**
     * Generate a bill for a visit. Idempotent. May optionally auto-transition
     * the visit to BILLED if all prerequisite conditions hold.
     */
    public function generate(int $visitId, int $actorId, array $extras = []): OpdBill
    {
        return DB::transaction(function () use ($visitId, $actorId, $extras) {
            $visit = OpdVisit::query()->lockForUpdate()->find($visitId);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            // Disallow generation on terminal states
            if (OpdVisitStatusEnum::isTerminal((string) $visit->status)) {
                throw new ApiException(
                    "Cannot generate a bill for a terminal-state visit ({$visit->status}).",
                    422,
                );
            }

            $bill = $this->billRepo->generateFromVisit($visitId, $actorId, $extras);

            // Auto-transition COMPLETED → BILLED if no manual discount and visit is in COMPLETED
            $autoBill = ($extras['auto_bill'] ?? false) === true;
            if ($autoBill && $visit->status === OpdVisitStatusEnum::COMPLETED) {
                $this->visitRepo->transitionStatus(
                    $visitId,
                    OpdVisitStatusEnum::BILLED,
                    $actorId,
                    'Auto-transitioned after bill generation',
                    ['bill_id' => $bill->id],
                );
            }

            return $bill;
        });
    }

    /**
     * Record a payment against a bill.
     */
    public function recordPayment(int $billId, array $data, ?int $actorId): OpdBill
    {
        // Validate using OpdBillPaymentValidator (payment-specific rules)
        $rules = (new OpdBillPaymentValidator())->rules();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ApiException(
                'Validation failed: ' . implode('; ', $validator->errors()->all()),
                422,
                $validator->errors()->toArray(),
            );
        }

        return $this->billRepo->recordPayment($billId, array_merge($data, [
            'paid_by' => $data['paid_by'] ?? $actorId,
        ]));
    }

    /**
     * Record a single collection made up of multiple payment modes at once
     * (e.g. part cash + part card). Each entry is validated and recorded via
     * recordPayment() so bill totals/status and the OPD visit audit log stay
     * consistent with single-mode payments.
     */
    public function recordSplitPayment(int $billId, array $payments, int $actorId): OpdBill
    {
        if (empty($payments)) {
            throw new ApiException('At least one payment is required.', 422);
        }

        $bill = $this->billRepo->findById($billId);
        if (!$bill) {
            throw new ApiException('Bill not found.', 404);
        }

        $sum = array_sum(array_map(fn ($p) => (float) ($p['amount'] ?? 0), $payments));
        if ($sum <= 0) {
            throw new ApiException('Total payment amount must be greater than zero.', 422);
        }
        if (round($sum, 2) > round((float) $bill->balance, 2)) {
            throw new ApiException(
                "Total payment ({$sum}) exceeds the outstanding balance ({$bill->balance}).",
                422,
            );
        }

        return DB::transaction(function () use ($billId, $payments, $actorId) {
            $result = null;
            foreach ($payments as $payment) {
                $result = $this->recordPayment($billId, array_merge($payment, [
                    'paid_at' => $payment['paid_at'] ?? now(),
                ]), $actorId);
            }
            return $result;
        });
    }

    /**
     * Render a payment receipt as a downloadable PDF.
     */
    public function renderReceiptPdf(int $billId)
    {
        $bill = $this->billRepo->findById($billId);
        if (!$bill) {
            throw new ApiException('Bill not found.', 404);
        }

        $bill->loadMissing(['items', 'payments', 'visit.patient', 'visit.doctor']);

        $pdf = Pdf::loadView('pdf.opd_receipt', ['bill' => $bill]);

        return $pdf->stream("receipt-{$bill->bill_no}.pdf");
    }

    /**
     * Apply a discount to a bill. Discounts within
     * DISCOUNT_APPROVAL_THRESHOLD_PERCENT of the subtotal are applied
     * immediately; larger discounts are held as pending until a supervisor
     * approves them via approveDiscount().
     */
    public function applyDiscount(int $billId, float $amount, string $type, ?string $reason, int $actorId): OpdBill
    {
        return DB::transaction(function () use ($billId, $amount, $type, $reason, $actorId) {
            $bill = $this->billRepo->newQuery()->lockForUpdate()->find($billId);
            if (!$bill) {
                throw new ApiException('Bill not found.', 404);
            }
            if (in_array($bill->status, [OpdBillStatusEnum::PAID, OpdBillStatusEnum::WAIVED], true)) {
                throw new ApiException("Cannot apply a discount to a bill that is already {$bill->status}.", 422);
            }
            if (!in_array($type, ['flat', 'percent'], true)) {
                throw new ApiException('Discount type must be flat or percent.', 422);
            }
            if ($amount <= 0) {
                throw new ApiException('Discount amount must be greater than zero.', 422);
            }

            $subtotal = (float) $bill->subtotal;
            $discountAmount = $type === 'percent'
                ? round($subtotal * $amount / 100, 2)
                : round($amount, 2);

            if ($discountAmount > $subtotal) {
                throw new ApiException('Discount cannot exceed the bill subtotal.', 422);
            }

            $thresholdAmount = round($subtotal * self::DISCOUNT_APPROVAL_THRESHOLD_PERCENT / 100, 2);
            $visit = OpdVisit::query()->find($bill->opd_visit_id);

            if ($discountAmount <= $thresholdAmount) {
                $bill->discount              = $discountAmount;
                $bill->discount_type         = $type;
                $bill->discount_reason       = $reason;
                $bill->discount_status       = 'approved';
                $bill->discount_approved_by  = $actorId;
                $bill->discount_approved_at  = now();
                $bill->pending_discount      = 0;
                $bill->total   = max(0.0, round($subtotal - $discountAmount + (float) $bill->tax, 2));
                $bill->balance = max(0.0, round($bill->total - (float) $bill->paid, 2));
                $bill->save();

                if ($visit) {
                    $this->visitRepo->logAudit(
                        $visit,
                        OpdVisitActionEnum::DISCOUNT_APPROVED,
                        null,
                        null,
                        $actorId,
                        "Discount of {$discountAmount} auto-approved (within threshold)",
                        ['bill_id' => $bill->id, 'amount' => $discountAmount],
                    );
                }
            } else {
                $bill->discount_type    = $type;
                $bill->discount_reason  = $reason;
                $bill->discount_status  = 'pending_approval';
                $bill->pending_discount = $discountAmount;
                $bill->save();

                if ($visit) {
                    $this->visitRepo->logAudit(
                        $visit,
                        OpdVisitActionEnum::DISCOUNT_REQUESTED,
                        null,
                        null,
                        $actorId,
                        "Discount of {$discountAmount} requires supervisor approval (exceeds threshold)",
                        ['bill_id' => $bill->id, 'amount' => $discountAmount],
                    );
                }
            }

            return $bill->fresh(['items', 'payments']);
        });
    }

    /**
     * Approve a pending discount, applying it to the bill total/balance.
     */
    public function approveDiscount(int $billId, int $actorId): OpdBill
    {
        return DB::transaction(function () use ($billId, $actorId) {
            $bill = $this->billRepo->newQuery()->lockForUpdate()->find($billId);
            if (!$bill) {
                throw new ApiException('Bill not found.', 404);
            }
            if ($bill->discount_status !== 'pending_approval') {
                throw new ApiException('This bill has no pending discount to approve.', 422);
            }

            $discountAmount = (float) $bill->pending_discount;
            $bill->discount             = $discountAmount;
            $bill->discount_status      = 'approved';
            $bill->discount_approved_by = $actorId;
            $bill->discount_approved_at = now();
            $bill->pending_discount     = 0;
            $bill->total   = max(0.0, round((float) $bill->subtotal - $discountAmount + (float) $bill->tax, 2));
            $bill->balance = max(0.0, round($bill->total - (float) $bill->paid, 2));
            $bill->save();

            $visit = OpdVisit::query()->find($bill->opd_visit_id);
            if ($visit) {
                $this->visitRepo->logAudit(
                    $visit,
                    OpdVisitActionEnum::DISCOUNT_APPROVED,
                    null,
                    null,
                    $actorId,
                    "Discount of {$discountAmount} approved by supervisor",
                    ['bill_id' => $bill->id, 'amount' => $discountAmount],
                );
            }

            return $bill->fresh(['items', 'payments']);
        });
    }

    /**
     * Reject a pending discount request; the bill is left unchanged.
     */
    public function rejectDiscount(int $billId, int $actorId, string $reason): OpdBill
    {
        return DB::transaction(function () use ($billId, $actorId, $reason) {
            $bill = $this->billRepo->newQuery()->lockForUpdate()->find($billId);
            if (!$bill) {
                throw new ApiException('Bill not found.', 404);
            }
            if ($bill->discount_status !== 'pending_approval') {
                throw new ApiException('This bill has no pending discount to reject.', 422);
            }

            $bill->discount_status  = 'rejected';
            $bill->pending_discount = 0;
            $bill->save();

            $visit = OpdVisit::query()->find($bill->opd_visit_id);
            if ($visit) {
                $this->visitRepo->logAudit(
                    $visit,
                    OpdVisitActionEnum::DISCOUNT_REJECTED,
                    null,
                    null,
                    $actorId,
                    'Discount rejected: ' . $reason,
                    ['bill_id' => $bill->id],
                );
            }

            return $bill->fresh(['items', 'payments']);
        });
    }

    /**
     * Waive/cancel a bill with a reason. Not allowed on fully paid bills.
     */
    public function waive(int $billId, int $actorId, string $reason): OpdBill
    {
        if (trim($reason) === '') {
            throw new ApiException('A reason is required to waive a bill.', 422);
        }
        return $this->billRepo->cancel($billId, $actorId, $reason);
    }

    /**
     * Transition visit to BILLED status manually.
     */
    public function markVisitBilled(int $visitId, int $actorId): \App\Models\OpdVisit
    {
        return $this->billRepo->markBilled($visitId, $actorId);
    }

    public function find(int $id): ?OpdBill
    {
        return $this->billRepo->findById($id);
    }

    public function findForVisit(int $visitId): ?OpdBill
    {
        return $this->billRepo->forVisit($visitId);
    }

    public function list()
    {
        return $this->billRepo->getList();
    }

    public function payments(int $billId)
    {
        return $this->paymentRepo->newQuery()
            ->where('opd_bill_id', $billId)
            ->orderByDesc('paid_at')
            ->get();
    }

    /**
     * Render the printable bill HTML (delegates to a simple view or template string).
     */
    public function renderPrint(int $billId): string
    {
        $bill = $this->billRepo->findById($billId);
        if (!$bill) {
            throw new ApiException('Bill not found.', 404);
        }

        $bill->loadMissing(['items', 'payments', 'visit.patient', 'visit.doctor']);

        $patient = $bill->visit->patient ?? null;
        $patientName = $patient ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) : '-';
        $doctorName  = $bill->visit->doctor->name_en ?? $bill->visit->doctor->name ?? '-';
        $billDate    = $bill->billed_at ?? now()->toDateString();

        $itemsHtml = '';
        foreach ($bill->items as $it) {
            $itemsHtml .= sprintf(
                '<tr><td>%s</td><td>%s</td><td style="text-align:right">%s</td><td style="text-align:right">%.2f</td><td style="text-align:right">%.2f</td></tr>',
                e((string) $it->item_type),
                e((string) $it->description),
                e((string) $it->quantity),
                (float) $it->unit_price,
                (float) $it->line_total,
            );
        }

        $paymentsHtml = '';
        foreach ($bill->payments as $p) {
            $paymentsHtml .= sprintf(
                '<tr><td>%s</td><td>%s</td><td style="text-align:right">%.2f</td><td>%s</td></tr>',
                e((string) $p->payment_method),
                e((string) ($p->paid_at ?? '')),
                (float) $p->amount,
                e((string) ($p->reference_no ?? '')),
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Bill {$bill->bill_no}</title>
<style>
  body{font-family: sans-serif; margin: 24px; color: #222;}
  h1{margin: 0 0 8px 0;}
  table{width: 100%; border-collapse: collapse; margin-top: 8px;}
  th, td{border-bottom: 1px solid #ddd; padding: 6px 8px;}
  th{text-align: left; background: #f4f4f4;}
  .totals td{font-weight: bold;}
  .right{text-align: right;}
  .section{margin-top: 24px;}
</style>
</head>
<body>
  <h1>Bill {$bill->bill_no}</h1>
  <p>
    Patient: <strong>{$patientName}</strong><br>
    Doctor: <strong>{$doctorName}</strong><br>
    Visit date: <strong>{$bill->visit->visit_date}</strong><br>
    Bill date: <strong>{$billDate}</strong><br>
    Status: <strong>{$bill->status}</strong>
  </p>

  <div class="section">
    <h3>Line items</h3>
    <table>
      <thead><tr><th>Type</th><th>Description</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Amount</th></tr></thead>
      <tbody>{$itemsHtml}</tbody>
    </table>
  </div>

  <div class="section">
    <h3>Totals</h3>
    <table class="totals">
      <tr><td>Subtotal</td><td class="right">{$bill->subtotal}</td></tr>
      <tr><td>Discount</td><td class="right">{$bill->discount}</td></tr>
      <tr><td>Tax</td><td class="right">{$bill->tax}</td></tr>
      <tr><td>Total</td><td class="right">{$bill->total}</td></tr>
      <tr><td>Paid</td><td class="right">{$bill->paid}</td></tr>
      <tr><td>Balance</td><td class="right">{$bill->balance}</td></tr>
    </table>
  </div>

  <div class="section">
    <h3>Payments</h3>
    <table>
      <thead><tr><th>Method</th><th>Date</th><th class="right">Amount</th><th>Reference</th></tr></thead>
      <tbody>{$paymentsHtml}</tbody>
    </table>
  </div>
</body>
</html>
HTML;
    }
}