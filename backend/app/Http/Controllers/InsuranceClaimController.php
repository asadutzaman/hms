<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\InsuranceClaimResource;
use App\Repositories\InsuranceClaimRepository;
use App\Services\Insurance\InsuranceClaimService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\InsuranceClaimValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InsuranceClaimController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    use RestControllerTrait;

    public function __construct(InsuranceClaimRepository $repository, InsuranceClaimValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = InsuranceClaimResource::class;
    }

    /** Override show — eager-load patient/insurance/pre-authorization relations. */
    public function show($id)
    {
        try {
            $result = $this->repository->withRelations((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /insurance-claim/by-patient/{patientId} */
    public function byPatient(Request $request, $patientId)
    {
        try {
            $rows = $this->repository->forPatient((int) $patientId);
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /insurance-claim/tracking — F-20-04 status-bucket summary + aging list. */
    public function tracking()
    {
        try {
            $summary = $this->repository->trackingSummary();
            return $this->successResponse($summary);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /insurance-claim/{id}/form-pdf — F-20-03 claim document bundle. */
    public function formPdf($id)
    {
        try {
            return app(InsuranceClaimService::class)->renderClaimPdf((int) $id);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /insurance-claim/by-bill?billable_type=opd_bill&billable_id=1 */
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

    /** Override store — raises a draft claim against an existing OPD/IPD bill. */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(InsuranceClaimService::class)->createFromBill($request->all(), $actorId);

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

    /** POST /insurance-claim/{id}/submit — "submitted electronically" per F-08-05. */
    public function submit($id)
    {
        DB::beginTransaction();
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(InsuranceClaimService::class)->submit((int) $id, $actorId);

            DB::commit();
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /insurance-claim/{id}/status — Body: { claim_status, approved_amount?, notes? }.
     * 'settled' is deliberately excluded — Sprint 9 (F-20-05) requires
     * settlement to always go through InsuranceClaimSettlementController's
     * settle() endpoint, which creates the bank-receipt-matched settlement
     * record (and bills the patient for any shortfall) atomically with the
     * status transition. Allowing 'settled' here too would let a claim end
     * up in 'settled' status with no settlement record behind it.
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'claim_status'    => ['required', Rule::in(['under_review', 'approved', 'partially_approved', 'rejected'])],
                'approved_amount' => ['nullable', 'numeric', 'min:0'],
                'notes'           => ['nullable', 'string'],
            ]);

            $result = app(InsuranceClaimService::class)->updateStatus(
                (int) $id,
                $request->input('claim_status'),
                $request->has('approved_amount') ? (float) $request->input('approved_amount') : null,
                $request->input('notes')
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
