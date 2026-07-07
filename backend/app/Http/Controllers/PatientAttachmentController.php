<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\PatientAttachmentResource;
use App\Repositories\PatientAttachmentRepository;
use App\Services\Attachment\PatientAttachmentService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\PatientAttachmentValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientAttachmentController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    use RestControllerTrait;

    public function __construct(PatientAttachmentRepository $repository, PatientAttachmentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PatientAttachmentResource::class;
    }

    /** GET /patient-attachment/by-patient/{patientId}?category= */
    public function byPatient(Request $request, $patientId)
    {
        try {
            $rows = $this->repository->forPatient((int) $patientId, $request->query('category'));
            $response = $this->resource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /patient-attachment/upload — multipart form: file, patient_id, category?, title?, description? */
    public function upload(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(PatientAttachmentService::class)->upload($request->file('file'), $request->all(), $actorId);

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
