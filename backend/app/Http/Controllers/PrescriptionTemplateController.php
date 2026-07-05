<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Exceptions\ValidatorException;
use App\Services\Opd\PrescriptionTemplateService;
use App\Services\SessionService;
use App\Validators\PrescriptionTemplateValidator;
use App\Repositories\PrescriptionTemplateRepository;
use App\Http\Resources\PrescriptionTemplateResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PrescriptionTemplateController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PrescriptionTemplateRepository $repository, PrescriptionTemplateValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PrescriptionTemplateResource::class;
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages());
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(PrescriptionTemplateService::class)->create($request->all(), $actorId);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /prescription-template/{id}/apply
     */
    public function apply($id)
    {
        try {
            $items = app(PrescriptionTemplateService::class)->apply((int) $id);
            return $this->successResponse(['items' => $items]);
        } catch (ApiException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
