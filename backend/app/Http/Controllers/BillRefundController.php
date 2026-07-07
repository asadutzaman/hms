<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\BillRefundResource;
use App\Repositories\BillRefundRepository;
use App\Services\Billing\RefundService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BillRefundController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(BillRefundRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = BillRefundResource::class;
    }

    /** GET /bill-refund/by-bill?billable_type=opd_bill&billable_id=1 */
    public function byBill(Request $request)
    {
        try {
            $request->validate([
                'billable_type' => ['required', Rule::in(['opd_bill', 'ipd_bill'])],
                'billable_id'   => ['required', 'integer'],
            ]);
            $rows = $this->repository->forBillable($request->input('billable_type'), (int) $request->input('billable_id'));
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /bill-refund/pending */
    public function pending(Request $request)
    {
        try {
            $rows = $this->repository->pending();
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /bill-refund/request — Body: { billable_type, billable_id, amount, reason } */
    public function request(Request $request)
    {
        try {
            $request->validate([
                'billable_type' => ['required', Rule::in(['opd_bill', 'ipd_bill'])],
                'billable_id'   => ['required', 'integer'],
                'amount'        => ['required', 'numeric', 'min:0.01'],
                'reason'        => ['required', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(RefundService::class)->requestRefund(
                $request->input('billable_type'),
                (int) $request->input('billable_id'),
                (float) $request->input('amount'),
                $request->input('reason'),
                $actorId
            );

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /bill-refund/{id}/approve */
    public function approve($id)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(RefundService::class)->approve((int) $id, $actorId);

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /bill-refund/{id}/reject — Body: { reason } */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate(['reason' => ['required', 'string']]);
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(RefundService::class)->reject((int) $id, $actorId, $request->input('reason'));

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
