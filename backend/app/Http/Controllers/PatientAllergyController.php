<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Validators\PatientAllergyValidator;
use App\Repositories\PatientAllergyRepository;
use App\Http\Resources\PatientAllergyResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PatientAllergyController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PatientAllergyRepository $repository, PatientAllergyValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PatientAllergyResource::class;
    }

    /**
     * Override store — default recorded_by/recorded_at to the acting user/now
     * when the client doesn't send them explicitly.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();
            $data['recorded_by'] = $data['recorded_by'] ?? Auth::id();
            $data['recorded_at'] = $data['recorded_at'] ?? now();

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
