<?php

namespace App\Repositories;

use App\Enums\OpdBillStatusEnum;
use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Enums\OpdPaymentMethodEnum;
use App\Exceptions\ApiException;
use App\Models\Appointment;
use App\Models\OpdBill;
use App\Models\OpdBillItem;
use App\Models\OpdBillPayment;
use App\Models\OpdInvestigationOrderItem;
use App\Models\OpdPrescriptionItem;
use App\Models\OpdVisit;
use App\Services\ODataService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OpdBillRepository extends BaseRepository
{
    /**
     * Item-type constants (mirror the enum in opd_bill_items.item_type migration column).
     */
    public const ITEM_TYPE_CONSULTATION  = 'consultation';
    public const ITEM_TYPE_PRESCRIPTION  = 'prescription';
    public const ITEM_TYPE_INVESTIGATION = 'investigation';
    public const ITEM_TYPE_OTHER         = 'other';

    /**
     * @var OpdBill
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'bill_no',
        'status',
    ];

    public function __construct()
    {
        $this->model = new OpdBill();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /* ---------- Queries ---------- */

    public function forVisit(int $visitId): ?OpdBill
    {
        return $this->newQuery()
            ->with(['items', 'payments'])
            ->where('opd_visit_id', $visitId)
            ->first();
    }

    /* ---------- Bill generation ---------- */

    /**
     * Generate a bill for a visit atomically. Snapshots:
     *   - consultation fee (from linked appointment, or 0 if walk-in)
     *   - active prescription items (one bill line per Rx item)
     *   - active investigation order items (one bill line per test)
     *
     * Idempotent: returns the existing bill if one already exists for the visit.
     *
     * After saving, writes a bill_generated audit log on the parent visit.
     * Does NOT auto-transition visit status — the service layer decides whether to
     * transition COMPLETED → BILLED (e.g., only if no manual discount needed).
     */
    public function generateFromVisit(int $visitId, int $actorId, array $extras = []): OpdBill
    {
        return DB::transaction(function () use ($visitId, $actorId, $extras) {
            $existing = $this->forVisit($visitId);
            if ($existing) {
                return $existing->loadMissing(['items', 'payments']);
            }

            $visit = OpdVisit::query()->lockForUpdate()->findOrFail($visitId);

            // 1) Consultation line item
            $consultationFee = $this->resolveConsultationFee($visit);

            $items = [];

            if ((float) $consultationFee > 0) {
                $items[] = [
                    'item_type'   => self::ITEM_TYPE_CONSULTATION,
                    'description' => 'Doctor consultation fee',
                    'quantity'    => 1,
                    'unit_price'  => $consultationFee,
                    'line_total'  => $consultationFee,
                    'source_type' => Appointment::class,
                    'source_id'   => $visit->appointment_id,
                ];
            }

// 2) Prescription items (only ACTIVE ones, ordered by sequence).
            //    Query via the parent prescription row because the items table
            //    is not denormalized with opd_visit_id (FK lives on
            //    opd_prescriptions).
            $rxItems = OpdPrescriptionItem::query()
                ->whereHas('prescription', function ($q) use ($visitId) {
                    $q->where('opd_visit_id', $visitId);
                })
                ->where('status', 1)
                ->orderBy('sequence')
                ->get();

            foreach ($rxItems as $rx) {
                $qty   = (float) ($rx->dose_value ?? 1);
                $price = (float) ($rx->unit_price ?? 0);
                if ($qty <= 0)   { $qty = 1; }
                $items[] = [
                    'item_type'   => self::ITEM_TYPE_PRESCRIPTION,
                    'description' => trim(($rx->drug_name ?? '') . ' ' . ($rx->strength ?? '')),
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'line_total'  => round($qty * $price, 2),
                    'source_type' => OpdPrescriptionItem::class,
                    'source_id'   => $rx->id,
                ];
            }

            // 3) Investigation order items (only ACTIVE ones). Same join-via-parent
            //    approach as prescription items above.
            $invItems = OpdInvestigationOrderItem::query()
                ->whereHas('order', function ($q) use ($visitId) {
                    $q->where('opd_visit_id', $visitId);
                })
                ->where('status', 1)
                ->orderBy('sequence')
                ->get();

            foreach ($invItems as $inv) {
                $price = (float) ($inv->unit_price ?? 0);
                $items[] = [
                    'item_type'   => self::ITEM_TYPE_INVESTIGATION,
                    'description' => $inv->test_name ?? 'Investigation',
                    'quantity'    => 1,
                    'unit_price'  => $price,
                    'line_total'  => $price,
                    'source_type' => OpdInvestigationOrderItem::class,
                    'source_id'   => $inv->id,
                ];
            }

            // 4) Manual extras (any additional charges passed by caller)
            foreach (($extras['items'] ?? []) as $i => $extra) {
                $qty   = (float) ($extra['quantity'] ?? 1);
                $price = (float) ($extra['unit_price'] ?? 0);
                $items[] = [
                    'item_type'   => $extra['item_type'] ?? self::ITEM_TYPE_OTHER,
                    'description' => $extra['description'] ?? 'Additional charge',
                    'quantity'    => $qty > 0 ? $qty : 1,
                    'unit_price'  => $price,
                    'line_total'  => round($qty * $price, 2),
                    'source_type' => $extra['source_type'] ?? null,
                    'source_id'   => $extra['source_id']   ?? null,
                ];
            }

            // Totals
            $subtotal = array_sum(array_column($items, 'line_total'));
            $discount = (float) ($extras['discount'] ?? 0);
            $tax      = (float) ($extras['tax']      ?? 0);
            $total    = max(0.0, round($subtotal - $discount + $tax, 2));
            $billNo   = $this->generateBillNo($visit->visit_date instanceof Carbon
                ? $visit->visit_date->toDateString()
                : (string) $visit->visit_date);

            $bill = OpdBill::query()->forceCreate([
                'organogram_id' => $visit->organogram_id,
                'opd_visit_id'  => $visitId,
                'bill_no'       => $billNo,
                'subtotal'      => round($subtotal, 2),
                'discount'      => round($discount, 2),
                'tax'           => round($tax, 2),
                'total'         => $total,
                'paid'          => 0,
                'balance'       => $total,
                'status'        => OpdBillStatusEnum::UNPAID,
                'billed_by'     => $actorId,
                'billed_at'     => now(),
                'status_flag'   => 1,
                'sort_order'    => 0,
            ]);

            foreach ($items as $i => $row) {
                OpdBillItem::query()->forceCreate(array_merge($row, [
                    'organogram_id' => $visit->organogram_id,
                    'opd_bill_id'   => $bill->id,
                    'sequence'      => $i + 1,
                    'status_flag'   => 1,
                    'sort_order'    => $i + 1,
                ]));
            }

            // Audit log
            app(OpdVisitRepository::class)->logAudit(
                $visit,
                OpdVisitActionEnum::BILL_GENERATED,
                null,
                null,
                $actorId,
                'Bill generated',
                [
                    'bill_id' => $bill->id,
                    'bill_no' => $billNo,
                    'total'   => $total,
                    'items'   => count($items),
                ],
            );

            return $bill->fresh(['items', 'payments']);
        });
    }

    /* ---------- Payments ---------- */

    /**
     * Record a payment against a bill. Recomputes paid/balance/status from the
     * sum of all payment rows. Writes payment_recorded audit log.
     */
    public function recordPayment(int $billId, array $data): OpdBill
    {
        return DB::transaction(function () use ($billId, $data) {
            $bill = $this->newQuery()->lockForUpdate()->findOrFail($billId);

            if ($bill->status === OpdBillStatusEnum::PAID) {
                throw new ApiException(
                    "Bill {$bill->bill_no} is already fully paid.",
                    422,
                );
            }

            $amount = (float) ($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new ApiException('Payment amount must be greater than zero.', 422);
            }

            $visit = OpdVisit::query()->find($bill->opd_visit_id);

            OpdBillPayment::query()->forceCreate([
                'organogram_id'  => $bill->organogram_id,
                'opd_bill_id'    => $bill->id,
                'amount'         => round($amount, 2),
                'payment_method' => $data['payment_method'] ?? OpdPaymentMethodEnum::CASH,
                'reference_no'   => $data['reference_no']   ?? null,
                'notes'          => $data['notes']          ?? null,
                'paid_by'        => $data['paid_by']        ?? null,
                'paid_at'        => $data['paid_at']        ?? now(),
                'status_flag'    => 1,
                'sort_order'     => 0,
            ]);

            // Recompute totals from all active payments
            $sumPaid = (float) OpdBillPayment::query()
                ->where('opd_bill_id', $bill->id)
                ->where('status_flag', 1)
                ->sum('amount');

            $total  = (float) $bill->total;
            $sumPaid = round($sumPaid, 2);

            if ($sumPaid >= $total) {
                $newStatus = OpdBillStatusEnum::PAID;
                $balance   = 0.0;
            } elseif ($sumPaid > 0) {
                $newStatus = OpdBillStatusEnum::PARTIAL;
                $balance   = round($total - $sumPaid, 2);
            } else {
                $newStatus = OpdBillStatusEnum::UNPAID;
                $balance   = $total;
            }

            $bill->paid    = $sumPaid;
            $bill->balance = $balance;
            $bill->status  = $newStatus;
            $bill->save();

            if ($visit) {
                app(OpdVisitRepository::class)->logAudit(
                    $visit,
                    OpdVisitActionEnum::PAYMENT_RECORDED,
                    null,
                    null,
                    (int) ($data['paid_by'] ?? 0),
                    "Payment of {$amount} via " . ($data['payment_method'] ?? OpdPaymentMethodEnum::CASH),
                    [
                        'bill_id' => $bill->id,
                        'amount'  => $amount,
                        'status'  => $newStatus,
                    ],
                );
            }

            return $bill->fresh(['items', 'payments']);
        });
    }

    /* ---------- Cancellation / waiver ---------- */

    public function cancel(int $billId, int $actorId, string $reason): OpdBill
    {
        return DB::transaction(function () use ($billId, $actorId, $reason) {
            $bill = $this->newQuery()->lockForUpdate()->findOrFail($billId);

            if ($bill->status === OpdBillStatusEnum::PAID) {
                throw new ApiException('Cannot cancel a fully paid bill — issue a refund instead.', 422);
            }

            $bill->status = OpdBillStatusEnum::WAIVED;
            $bill->save();

            $visit = OpdVisit::query()->find($bill->opd_visit_id);
            if ($visit) {
                app(OpdVisitRepository::class)->logAudit(
                    $visit,
                    OpdVisitActionEnum::WAIVED,
                    null,
                    null,
                    $actorId,
                    'Bill waived: ' . $reason,
                    ['bill_id' => $bill->id, 'reason' => $reason],
                );
            }

            return $bill->fresh();
        });
    }

    /* ---------- Mark bill paid (billed transition helper) ---------- */

    public function markBilled(int $visitId, int $actorId): OpdVisit
    {
        $visit = OpdVisit::query()->findOrFail($visitId);
        if (!OpdVisitStatusEnum::canTransition((string) $visit->status, OpdVisitStatusEnum::BILLED)) {
            throw new RuntimeException("Cannot mark billed from {$visit->status}");
        }
        return app(OpdVisitRepository::class)->transitionStatus(
            $visitId,
            OpdVisitStatusEnum::BILLED,
            $actorId,
            null,
            [],
            OpdVisitActionEnum::STATUS_CHANGE,
        );
    }

    /* ---------- Helpers ---------- */

    protected function resolveConsultationFee(OpdVisit $visit): float
    {
        if ($visit->appointment_id) {
            $appt = Appointment::query()->find($visit->appointment_id);
            if ($appt && (float) $appt->consultation_fee > 0) {
                return (float) $appt->consultation_fee;
            }
        }
        return 0.0;
    }

    protected function generateBillNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'OPD_BILL')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'label'         => 'OPD_BILL',
                    'prefix'        => 'BILL',
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
            return "BILL-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
