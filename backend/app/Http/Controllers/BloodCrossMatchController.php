<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\BloodCrossMatchResource;
use App\Models\BloodCrossMatch;
use App\Services\BloodBank\BloodCrossMatchService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BloodCrossMatchController extends Controller
{
    use TraitRestResponse;

    /** GET /blood-cross-match/by-patient/{patientId} */
    public function byPatient($patientId)
    {
        try {
            $rows = BloodCrossMatch::query()->with('bloodUnit')->where('patient_id', $patientId)->orderByDesc('created_at')->get();
            $response = BloodCrossMatchResource::collection($rows)->toArray(request());
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /blood-cross-match — Body: { patient_id, blood_unit_id,
     * patient_blood_group?, cross_match_result, method?, notes? }
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'patient_id'          => ['required', 'integer', 'exists:patients,id'],
                'blood_unit_id'       => ['required', 'integer', 'exists:blood_units,id'],
                'patient_blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
                'cross_match_result'  => ['required', Rule::in(['compatible', 'incompatible'])],
                'method'              => ['nullable', 'string', 'max:32'],
                'notes'               => ['nullable', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BloodCrossMatchService::class)->performCrossMatch($request->all(), $actorId);

            return $this->successResponse((new BloodCrossMatchResource($result))->toArray($request));
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
