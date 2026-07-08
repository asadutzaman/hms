<?php

namespace App\Http\Controllers\PatientPortal;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentTransactionResource;
use App\Models\Appointment;
use App\Models\IpdBill;
use App\Models\OpdBill;
use App\Models\PaymentTransaction;
use App\Services\Payment\PaymentGatewayService;
use App\Services\PatientSessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * F-17-05 Online Payment via Portal / F-02-09 Online Payment During
 * Booking — one shared initiate/confirm flow for all three payable types
 * a patient can pay for (opd_bill, ipd_bill, appointment), always scoped
 * to the authenticated patient's own records (never a trusted payable_id
 * from an unrelated patient).
 */
class PatientPortalPaymentController extends Controller
{
    use TraitRestResponse;

    /** POST /patient-portal/payments/initiate — Body: { payable_type, payable_id, amount? } */
    public function initiate(Request $request)
    {
        try {
            $request->validate([
                'payable_type' => ['required', Rule::in(['opd_bill', 'ipd_bill', 'appointment'])],
                'payable_id'   => ['required', 'integer'],
                'amount'       => ['nullable', 'numeric', 'min:0.01'],
            ]);

            $patientId = (new PatientSessionService())->init()->getPatientId();
            $payableType = $request->input('payable_type');
            $payableId = (int) $request->input('payable_id');

            $amount = $this->resolveAmountAndAssertOwnership($payableType, $payableId, $patientId, $request->input('amount'));

            $transaction = app(PaymentGatewayService::class)->initiate($payableType, $payableId, $amount, $patientId, null);

            return $this->successResponse((new PaymentTransactionResource($transaction))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /patient-portal/payments/{transactionRef}/confirm — Body: { outcome: 'success'|'failure' } */
    public function confirm(Request $request, $transactionRef)
    {
        try {
            $request->validate(['outcome' => ['required', Rule::in(['success', 'failure'])]]);

            $patientId = (new PatientSessionService())->init()->getPatientId();
            $transaction = PaymentTransaction::query()->where('transaction_ref', $transactionRef)->first();
            if (!$transaction || (int) $transaction->patient_id !== (int) $patientId) {
                return response()->json(['message' => 'Transaction not found.'], 404);
            }

            $result = app(PaymentGatewayService::class)->confirm($transactionRef, $request->input('outcome'), null, $request->input('failure_reason'));

            return $this->successResponse((new PaymentTransactionResource($result))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/payments — the authenticated patient's own transaction history. */
    public function index(Request $request)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();
            $rows = PaymentTransaction::query()->where('patient_id', $patientId)->orderByDesc('initiated_at')->get();
            $response = PaymentTransactionResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    protected function resolveAmountAndAssertOwnership(string $payableType, int $payableId, int $patientId, ?float $requestedAmount): float
    {
        if ($payableType === 'opd_bill') {
            $bill = OpdBill::query()->with('visit')->find($payableId);
            if (!$bill || (int) $bill->visit->patient_id !== $patientId) {
                throw new ApiException('Bill not found.', 404);
            }
            $balance = (float) $bill->balance;
            $amount = $requestedAmount ?? $balance;
            if ($amount > $balance) {
                throw new ApiException("Amount exceeds the outstanding balance ({$balance}).", 422);
            }
            return $amount;
        }

        if ($payableType === 'ipd_bill') {
            $bill = IpdBill::query()->with('admission')->find($payableId);
            if (!$bill || (int) $bill->admission->patient_id !== $patientId) {
                throw new ApiException('Bill not found.', 404);
            }
            $balance = (float) $bill->balance;
            $amount = $requestedAmount ?? $balance;
            if ($amount > $balance) {
                throw new ApiException("Amount exceeds the outstanding balance ({$balance}).", 422);
            }
            return $amount;
        }

        if ($payableType === 'appointment') {
            $appointment = Appointment::query()->find($payableId);
            if (!$appointment || (int) $appointment->patient_id !== $patientId) {
                throw new ApiException('Appointment not found.', 404);
            }
            $amount = $requestedAmount ?? (float) $appointment->consultation_fee;
            if ($amount <= 0) {
                throw new ApiException('A payment amount is required for this appointment.', 422);
            }
            return $amount;
        }

        throw new ApiException('Unknown payable type.', 422);
    }
}
