<?php

namespace App\Services\Payment;

use App\Enums\IpdPaymentMethodEnum;
use App\Enums\OpdPaymentMethodEnum;
use App\Exceptions\ApiException;
use App\Models\Appointment;
use App\Models\IpdBill;
use App\Models\OpdBill;
use App\Models\PaymentTransaction;
use App\Services\Ipd\IpdBillService;
use App\Services\Opd\OpdBillService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * F-17-05 Online Payment via Portal / F-02-09 Online Payment During
 * Booking. There is no real payment gateway account/API in this project —
 * this talks to a deterministic mock provider instead of Stripe/SSLCommerz/
 * bKash/etc. initiate() creates the transaction record a real gateway SDK
 * would return a checkout session for; confirm() is the callback/webhook
 * a real gateway would POST back after the user completes (or abandons)
 * checkout — here the caller passes the outcome directly since there's no
 * actual checkout UI to redirect through. Same scope-boundary-stubbing
 * pattern as every other "no real hardware/service exists" decision this
 * project has made (SMS, biometric attendance, analyzer interfacing).
 */
class PaymentGatewayService
{
    public function initiate(string $payableType, int $payableId, float $amount, ?int $patientId, ?int $actorId): PaymentTransaction
    {
        if ($amount <= 0) {
            throw new ApiException('Payment amount must be greater than zero.', 422);
        }

        return PaymentTransaction::query()->create([
            'transaction_ref' => $this->generateTransactionRef(),
            'payable_type'    => $payableType,
            'payable_id'      => $payableId,
            'patient_id'      => $patientId,
            'amount'          => round($amount, 2),
            'payment_status'  => 'initiated',
            'initiated_at'    => now(),
            'created_by'      => $actorId,
        ]);
    }

    /**
     * $outcome: 'success' | 'failure' — simulates the gateway's callback.
     * $actorId is null for patient-initiated payments (no staff
     * SessionService actor exists in that flow) — deliberately NOT passed
     * through as paid_by/created_by=0, since OpdBillPaymentValidator
     * requires paid_by to either be null or an existing users.id; 0 would
     * fail that validation.
     */
    public function confirm(string $transactionRef, string $outcome, ?int $actorId, ?string $failureReason = null): PaymentTransaction
    {
        return DB::transaction(function () use ($transactionRef, $outcome, $actorId, $failureReason) {
            $transaction = PaymentTransaction::query()->lockForUpdate()->where('transaction_ref', $transactionRef)->first();
            if (!$transaction) {
                throw new ApiException('Payment transaction not found.', 404);
            }
            if ($transaction->payment_status !== 'initiated') {
                throw new ApiException("This transaction has already been {$transaction->payment_status}.", 422);
            }

            if ($outcome === 'success') {
                $transaction->payment_status = 'success';
                $transaction->gateway_reference = 'MOCKGW-' . Str::upper(Str::random(10));
                $transaction->completed_at = now();
                $transaction->save();

                $this->applySideEffect($transaction, $actorId);
            } else {
                $transaction->payment_status = 'failed';
                $transaction->failure_reason = $failureReason ?? 'Payment declined.';
                $transaction->completed_at = now();
                $transaction->save();
            }

            return $transaction->fresh();
        });
    }

    protected function applySideEffect(PaymentTransaction $transaction, ?int $actorId): void
    {
        match ($transaction->payable_type) {
            'opd_bill' => $this->payOpdBill($transaction, $actorId),
            'ipd_bill' => $this->payIpdBill($transaction, $actorId),
            'appointment' => $this->confirmAppointment($transaction),
            default => throw new ApiException('Unknown payable type.', 422),
        };
    }

    protected function payOpdBill(PaymentTransaction $transaction, ?int $actorId): void
    {
        // NOTE: OpdBillPaymentValidator requires opd_bill_id + paid_at on
        // POST (its rules() branches on request()->method(), which is
        // 'POST' here regardless of which endpoint actually triggered this
        // service call) — both must be passed explicitly or recordPayment()
        // throws a validation error.
        app(OpdBillService::class)->recordPayment((int) $transaction->payable_id, [
            'opd_bill_id'    => (int) $transaction->payable_id,
            'amount'         => (float) $transaction->amount,
            'payment_method' => OpdPaymentMethodEnum::ONLINE,
            'transaction_no' => $transaction->transaction_ref,
            'reference_no'   => $transaction->gateway_reference,
            'paid_at'        => now(),
        ], $actorId);
    }

    protected function payIpdBill(PaymentTransaction $transaction, ?int $actorId): void
    {
        app(IpdBillService::class)->recordPayment((int) $transaction->payable_id, [
            'amount'         => (float) $transaction->amount,
            'payment_method' => IpdPaymentMethodEnum::ONLINE,
            'reference_no'   => $transaction->gateway_reference,
        ], $actorId);
    }

    /**
     * F-02-09 "booking confirmed on success" — the appointment must
     * already exist (created as 'pending' by the normal booking flow);
     * a successful payment flips it to 'confirmed'. Uses raw lowercase
     * status literals, not AppointmentStatusEnum — see
     * project_hms_sprint8_scope memory on why that enum's uppercase
     * constants don't match the DB's lowercase-only values.
     */
    protected function confirmAppointment(PaymentTransaction $transaction): void
    {
        $appointment = Appointment::query()->find($transaction->payable_id);
        if ($appointment && $appointment->status === 'pending') {
            $appointment->status = 'confirmed';
            $appointment->save();
        }
    }

    protected function generateTransactionRef(): string
    {
        return 'TXN-' . Carbon::now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }
}
