<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\IpdVitalResource;
use App\Repositories\IpdVitalRepository;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\IpdVitalValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IpdVitalController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdVitalRepository $repository, IpdVitalValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdVitalResource::class;
    }

    /** GET /ipd-vital/by-admission/{admissionId} */
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

    /** Override store — auto-computes BMI from weight/height, stamps recorded_by. */
    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();
            $data['recorded_by'] = (new SessionService())->init()->getUserId();
            $data['recorded_at'] = $data['recorded_at'] ?? now();

            if (!empty($data['weight_kg']) && !empty($data['height_cm'])) {
                $heightM = ((float) $data['height_cm']) / 100.0;
                if ($heightM > 0) {
                    $data['bmi'] = round(((float) $data['weight_kg']) / ($heightM * $heightM), 2);
                }
            }

            $result = $this->repository->create($data);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
