<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\OpdBillResource;
use App\Repositories\OpdBillRepository;
use App\Services\Billing\PackageBillingService;
use App\Services\Opd\OpdBillService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\OpdBillValidator;
use App\Validators\OpdBillPaymentValidator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpdBillController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdBillRepository $repository, OpdBillValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->resource   = OpdBillResource::class;
    }

    /**
     * POST /opd-bill/generate/{visitId} — generate bill for a visit.
     */
    public function generate(Request $request, $visitId)
    {
        DB::beginTransaction();
        try {
            $extras = $request->all();
            $actorId = Auth::id();
            $result = app(OpdBillService::class)->generate($visitId, $actorId, $extras);

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-bill/{id}/payment — record a payment.
     */
    public function recordPayment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, (new OpdBillPaymentValidator())->rules());
            $data = $request->all();
            $actorId = Auth::id();
            $result = app(OpdBillService::class)->recordPayment($id, $data, $actorId);

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-bill/{id}/payments — record a single collection made up of
     * multiple payment modes at once (e.g. part cash + part card).
     * Body: { payments: [{payment_method, amount, reference_no?, notes?}, ...] }
     */
    public function recordSplitPayment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'payments'                  => ['required', 'array', 'min:1'],
                'payments.*.payment_method' => ['required', 'string'],
                'payments.*.amount'         => ['required', 'numeric', 'min:0.01'],
            ]);

            $actorId = Auth::id();
            $result = app(OpdBillService::class)->recordSplitPayment(
                (int) $id,
                $request->input('payments'),
                $actorId,
            );

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /opd-bill/{id}/receipt-pdf
     */
    public function receiptPdf($id)
    {
        try {
            return app(OpdBillService::class)->renderReceiptPdf((int) $id);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-bill/{id}/discount — apply a discount. Auto-approved when
     * within the configured threshold, otherwise held pending approval.
     * Body: { amount, type: 'flat'|'percent', reason? }
     */
    public function applyDiscount(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => ['required', 'numeric', 'min:0.01'],
                'type'   => ['required', 'string', 'in:flat,percent'],
                'reason' => ['nullable', 'string', 'max:255'],
            ]);

            $actorId = Auth::id();
            $result = app(OpdBillService::class)->applyDiscount(
                (int) $id,
                (float) $request->input('amount'),
                $request->input('type'),
                $request->input('reason'),
                $actorId,
            );

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-bill/{id}/discount/approve
     */
    public function approveDiscount($id)
    {
        DB::beginTransaction();
        try {
            $actorId = Auth::id();
            $result = app(OpdBillService::class)->approveDiscount((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-bill/{id}/discount/reject — Body: { reason }
     */
    public function rejectDiscount(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'reason' => ['required', 'string', 'max:500'],
            ]);

            $actorId = Auth::id();
            $result = app(OpdBillService::class)->rejectDiscount((int) $id, $actorId, $request->input('reason'));

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-bill/{id}/waive — waive the bill.
     */
    public function waive(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'reason' => ['required', 'string', 'max:500'],
            ]);
            $reason = $request->input('reason');
            $actorId = Auth::id();
            $result = app(OpdBillService::class)->waive($id, $actorId, $reason);

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /opd-bill/{id}/apply-package — Body: { billing_package_id }
     * (F-08-06 Package & Bundle Billing.)
     */
    public function applyPackage(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate(['billing_package_id' => ['required', 'integer', 'exists:billing_packages,id']]);

            $result = app(PackageBillingService::class)->applyToOpdBill(
                (int) $id,
                (int) $request->input('billing_package_id')
            );

            DB::commit();
            $response = new $this->resource($result->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /opd-bill/by-visit/{visitId} — get bill by visit id.
     */
    public function byVisit(Request $request, $visitId)
    {
        try {
            $bill = app(OpdBillService::class)->findForVisit($visitId);
            if (!$bill) {
                $this->errorResponse('Bill not found for this visit.', 404);
            }
            $response = new $this->resource($bill, false);
            return $this->successResourceResponse($response);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /opd-bill/{id}/print — get printable bill HTML.
     */
    public function print(Request $request, $id)
    {
        try {
            $html = app(OpdBillService::class)->renderPrint($id);
            return response($html)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
