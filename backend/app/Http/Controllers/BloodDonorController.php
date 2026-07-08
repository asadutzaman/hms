<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\BloodDonorResource;
use App\Repositories\BloodDonorRepository;
use App\Services\BloodBank\BloodDonorService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BloodDonorController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(BloodDonorRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = BloodDonorResource::class;
    }

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

    /** GET /blood-donor/eligible */
    public function eligible(Request $request)
    {
        try {
            $rows = $this->repository->eligibleDonors();
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'         => ['required', 'string', 'max:255'],
                'gender'       => ['nullable', 'string', 'max:16'],
                'dob'          => ['nullable', 'date'],
                'blood_group'  => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
                'phone'        => ['nullable', 'string', 'max:30'],
                'address'      => ['nullable', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BloodDonorService::class)->register($request->all(), $actorId);

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /blood-donor/{id}/defer — Body: { reason?, until_date? } (empty reason clears deferral) */
    public function setDeferral(Request $request, $id)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BloodDonorService::class)->setDeferral(
                (int) $id,
                $request->input('reason'),
                $request->input('until_date'),
                $actorId
            );

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
