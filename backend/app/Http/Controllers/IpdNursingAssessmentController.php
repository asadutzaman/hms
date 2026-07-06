<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdNursingAssessmentResource;
use App\Repositories\IpdNursingAssessmentRepository;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdNursingAssessmentValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IpdNursingAssessmentController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdNursingAssessmentRepository $repository, IpdNursingAssessmentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdNursingAssessmentResource::class;
    }

    /** GET /ipd-nursing-assessment/by-admission/{admissionId} */
    public function byAdmission(Request $request, $admissionId)
    {
        try {
            $result = $this->repository->forAdmission((int) $admissionId);
            if (!$result) {
                return $this->successResponse(null);
            }
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Override store — one assessment per admission; auto-saving from the
     * frontend re-POSTs periodically, so this upserts rather than erroring
     * on the unique(admission_id) constraint after the first save.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();
            $actorId = (new SessionService())->init()->getUserId();
            $data['assessed_by'] = $actorId;
            $data['assessed_at'] = now();
            $data = $this->deriveRiskLevels($data);

            $existing = $this->repository->forAdmission((int) $data['admission_id']);
            if ($existing) {
                $existing->fill($data);
                $existing->save();
                $result = $existing;
            } else {
                $result = $this->repository->create($data);
            }

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $this->deriveRiskLevels($request->all());
            $result = $this->repository->show($id);
            $result->fill($data);
            $result->save();

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** Morse (fall) / Braden (pressure injury) score → risk-level bucket. */
    protected function deriveRiskLevels(array $data): array
    {
        if (isset($data['fall_risk_score'])) {
            $score = (int) $data['fall_risk_score'];
            $data['fall_risk_level'] = $score >= 45 ? 'high' : ($score >= 25 ? 'medium' : 'low');
        }
        if (isset($data['pressure_injury_risk_score'])) {
            $score = (int) $data['pressure_injury_risk_score'];
            $data['pressure_injury_risk_level'] = $score <= 12 ? 'high' : ($score <= 17 ? 'medium' : 'low');
        }
        return $data;
    }
}
