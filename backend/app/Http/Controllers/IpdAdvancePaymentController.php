<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdAdvancePaymentResource;
use App\Repositories\IpdAdvancePaymentRepository;
use App\Services\Ipd\IpdBillService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdAdvancePaymentValidator;
use Exception;
use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IpdAdvancePaymentController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdAdvancePaymentRepository $repository, IpdAdvancePaymentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdAdvancePaymentResource::class;
    }

    /**
     * Override store — receiving an advance is a domain action (received_by
     * stamping + admission audit log), not a plain field-mapped create.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(IpdBillService::class)->receiveAdvance((int) $request->input('admission_id'), $request->all(), $actorId);

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

    /** GET /ipd-advance-payment/by-admission/{admissionId} */
    public function byAdmission(Request $request, $admissionId)
    {
        try {
            $rows = $this->repository->forAdmission((int) $admissionId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /ipd-advance-payment/{id}/apply — Body: { amount? } (defaults to full unapplied balance) */
    public function apply(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => ['nullable', 'numeric', 'min:0.01'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $amount = $request->has('amount') ? (float) $request->input('amount') : null;
            $bill = app(IpdBillService::class)->applyAdvanceToBill((int) $id, $amount, $actorId);

            DB::commit();
            return $this->successResponse((new \App\Http\Resources\IpdBillResource($bill))->resolve());
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
