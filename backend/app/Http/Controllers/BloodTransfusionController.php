<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\BloodTransfusionResource;
use App\Models\BloodTransfusion;
use App\Services\BloodBank\BloodTransfusionService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BloodTransfusionController extends Controller
{
    use TraitRestResponse;

    /** GET /blood-transfusion/by-patient/{patientId} */
    public function byPatient($patientId)
    {
        try {
            $rows = BloodTransfusion::query()->with(['bloodUnit', 'crossMatch'])->where('patient_id', $patientId)->orderByDesc('started_at')->get();
            $response = BloodTransfusionResource::collection($rows)->toArray(request());
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /blood-transfusion — Body: { patient_id, blood_unit_id,
     * cross_match_id?, ipd_admission_id?, started_at?, administered_by? }
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'patient_id'        => ['required', 'integer', 'exists:patients,id'],
                'blood_unit_id'     => ['required', 'integer', 'exists:blood_units,id'],
                'cross_match_id'    => ['nullable', 'integer', 'exists:blood_cross_matches,id'],
                'ipd_admission_id'  => ['nullable', 'integer', 'exists:ipd_admissions,id'],
                'started_at'        => ['nullable', 'date'],
                'administered_by'   => ['nullable', 'integer', 'exists:employees,id'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BloodTransfusionService::class)->recordTransfusion($request->all(), $actorId);

            return $this->successResponse((new BloodTransfusionResource($result))->toArray($request));
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /blood-transfusion/{id}/complete — Body: { ended_at?, reaction_observed?, reaction_notes? } */
    public function complete(Request $request, $id)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BloodTransfusionService::class)->completeTransfusion((int) $id, $request->all(), $actorId);

            return $this->successResponse((new BloodTransfusionResource($result))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
