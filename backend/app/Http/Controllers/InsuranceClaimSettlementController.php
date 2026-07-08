<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\InsuranceClaimSettlementResource;
use App\Models\InsuranceClaimSettlement;
use App\Services\Insurance\InsuranceClaimSettlementService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InsuranceClaimSettlementController extends Controller
{
    use TraitRestResponse;

    /** GET /insurance-claim-settlement/by-claim/{claimId} */
    public function byClaim($claimId)
    {
        try {
            $rows = InsuranceClaimSettlement::query()->where('insurance_claim_id', $claimId)->orderByDesc('created_at')->get();
            $response = InsuranceClaimSettlementResource::collection($rows)->toArray(request());
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /insurance-claim-settlement — Body: { insurance_claim_id, bank_reference_no, bank_receipt_date, settled_amount, notes? } */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'insurance_claim_id' => ['required', 'integer', 'exists:insurance_claims,id'],
                'bank_reference_no'  => ['required', 'string', 'max:255'],
                'bank_receipt_date'  => ['required', 'date'],
                'settled_amount'     => ['required', 'numeric', 'min:0'],
                'notes'              => ['nullable', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(InsuranceClaimSettlementService::class)->settle(
                (int) $request->input('insurance_claim_id'),
                $request->all(),
                $actorId
            );

            $response = (new InsuranceClaimSettlementResource($result))->toArray($request);
            return $this->successResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
