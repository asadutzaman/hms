<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\PatientChronicConditionReadingResource;
use App\Http\Resources\PatientChronicConditionResource;
use App\Models\PatientChronicConditionReading;
use App\Repositories\PatientChronicConditionRepository;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientChronicConditionController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(PatientChronicConditionRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = PatientChronicConditionResource::class;
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

    /** GET /patient-chronic-condition/by-patient/{patientId} */
    public function byPatient($patientId)
    {
        try {
            $rows = $this->repository->forPatient((int) $patientId);
            $response = $this->resource::collection($rows)->toArray(request());
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'patient_id'        => ['required', 'integer', 'exists:patients,id'],
                'condition_name'    => ['required', 'string', 'max:255'],
                'icd10_code_id'     => ['nullable', 'integer', 'exists:icd10_codes,id'],
                'diagnosed_date'    => ['nullable', 'date'],
                'target_notes'      => ['nullable', 'string'],
                'condition_status'  => ['nullable', 'in:active,monitoring,resolved'],
                'notes'             => ['nullable', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $data = $request->all();
            $data['created_by'] = $actorId;
            $result = $this->repository->create($data);

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

    /** POST /patient-chronic-condition/{id}/reading — Body: { reading_date, reading_type, reading_value, unit?, notes? } */
    public function addReading(Request $request, $id)
    {
        try {
            $request->validate([
                'reading_date'  => ['required', 'date'],
                'reading_type'  => ['required', 'string', 'max:32'],
                'reading_value' => ['required', 'string', 'max:64'],
                'unit'          => ['nullable', 'string', 'max:32'],
                'notes'         => ['nullable', 'string'],
            ]);

            $this->repository->findById((int) $id);
            $actorId = (new SessionService())->init()->getUserId();

            $reading = PatientChronicConditionReading::query()->create([
                'condition_id'  => (int) $id,
                'reading_date'  => $request->input('reading_date'),
                'reading_type'  => $request->input('reading_type'),
                'reading_value' => $request->input('reading_value'),
                'unit'          => $request->input('unit'),
                'notes'         => $request->input('notes'),
                'recorded_by'   => $actorId,
                'created_by'    => $actorId,
            ]);

            return $this->successResponse((new PatientChronicConditionReadingResource($reading))->toArray($request));
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
