<?php

namespace App\Http\Controllers;

use App\Enums\IpdBillItemTypeEnum;
use App\Enums\IpdPaymentMethodEnum;
use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdBillResource;
use App\Repositories\IpdBillRepository;
use App\Services\Ipd\IpdBillService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdBillValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IpdBillController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['bill_status'];

    use RestControllerTrait;

    public function __construct(IpdBillRepository $repository, IpdBillValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdBillResource::class;
    }

    /** GET /ipd-bill/by-admission/{admissionId} */
    public function byAdmission(Request $request, $admissionId)
    {
        try {
            $bill = app(IpdBillService::class)->findForAdmission((int) $admissionId);
            if (!$bill) {
                $this->errorResponse('Bill not found for this admission.', 404);
            }
            $response = new $this->resource($bill, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-bill/{id}/refresh-room-charges */
    public function refreshRoomCharges(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $bill = $this->repository->show($id);
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->refreshRoomCharges((int) $bill->admission_id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-bill/{id}/item — the bill is identified by the route, not the body. */
    public function addItem(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'item_type'   => ['nullable', Rule::in(IpdBillItemTypeEnum::getKeys())],
                'description' => ['required', 'string', 'max:255'],
                'quantity'    => ['required', 'numeric', 'min:0.01'],
                'unit_price'  => ['required', 'numeric', 'min:0'],
            ]);
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->addManualItem((int) $id, $request->all(), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** DELETE /ipd-bill/{id}/item/{itemId} */
    public function removeItem(Request $request, $id, $itemId)
    {
        DB::beginTransaction();
        try {
            $result = app(IpdBillService::class)->removeItem((int) $id, (int) $itemId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-bill/{id}/payment — the bill is identified by the route, not the body. */
    public function recordPayment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount'         => ['required', 'numeric', 'min:0.01'],
                'payment_method' => ['required', Rule::in(IpdPaymentMethodEnum::getKeys())],
                'reference_no'   => ['nullable', 'string', 'max:100'],
                'notes'          => ['nullable', 'string', 'max:500'],
                'paid_at'        => ['nullable', 'date'],
            ]);
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->recordPayment((int) $id, $request->all(), $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
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
     * POST /ipd-bill/{id}/payments — record several payment modes at once.
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

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->recordSplitPayment(
                (int) $id,
                $request->input('payments'),
                $actorId,
            );

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-bill/{id}/discount — Body: { amount, type: 'flat'|'percent', reason? } */
    public function applyDiscount(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => ['required', 'numeric', 'min:0.01'],
                'type'   => ['required', 'string', 'in:flat,percent'],
                'reason' => ['nullable', 'string', 'max:255'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->applyDiscount(
                (int) $id,
                (float) $request->input('amount'),
                $request->input('type'),
                $request->input('reason'),
                $actorId,
            );

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-bill/{id}/discount/approve */
    public function approveDiscount($id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->approveDiscount((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-bill/{id}/discount/reject — Body: { reason } */
    public function rejectDiscount(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'reason' => ['required', 'string', 'max:500'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->rejectDiscount((int) $id, $actorId, $request->input('reason'));

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-bill/{id}/waive — Body: { reason } */
    public function waive(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'reason' => ['required', 'string', 'max:500'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->waive((int) $id, $actorId, $request->input('reason'));

            DB::commit();
            $response = new $this->resource($result, false);
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
     * POST /ipd-bill/{id}/apply-package — Body: { billing_package_id }
     * (F-08-06 Package & Bundle Billing.)
     */
    public function applyPackage(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate(['billing_package_id' => ['required', 'integer', 'exists:billing_packages,id']]);

            $result = app(\App\Services\Billing\PackageBillingService::class)->applyToIpdBill(
                (int) $id,
                (int) $request->input('billing_package_id')
            );

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
