<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Validators\OpdPrescriptionValidator;
use App\Repositories\OpdPrescriptionRepository;
use App\Http\Resources\OpdPrescriptionResource;
use App\Services\Opd\OpdPrescriptionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OpdPrescriptionController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdPrescriptionRepository $repository, OpdPrescriptionValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdPrescriptionResource::class;
    }

    /**
     * POST /opd-prescription/visit/{visitId} — create the prescription (with
     * items) if none exists for the visit yet, otherwise update the header
     * and replace the items. Keeps prescription writing a single call from
     * the OPD visit view.
     */
    public function saveForVisit(Request $request, $visitId)
    {
        try {
            $actorId = Auth::id();
            $data = $request->all();
            $items = $data['items'] ?? [];

            $existing = $this->repository->findByVisit((int) $visitId);
            $service = app(OpdPrescriptionService::class);

            if ($existing) {
                $service->update($existing->id, $data, $actorId);
                if (!empty($items)) {
                    $service->replaceItems($existing->id, $items, $actorId);
                }
                $result = $this->repository->findByVisit((int) $visitId);
            } else {
                $result = $service->create((int) $visitId, $data, $actorId);
            }

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /opd-prescription/{id}/pdf
     */
    public function pdf($id)
    {
        try {
            $actorId = Auth::id();
            return app(OpdPrescriptionService::class)->renderPdf((int) $id, $actorId);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
