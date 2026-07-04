<?php

namespace App\Http\Controllers;

use App\Enums\PatientActionEnum;
use App\Exceptions\ValidatorException;
use App\Http\Resources\PatientResource;
use App\Models\PatientAuditLog;
use App\Repositories\CodeSequenceRepository;
use App\Repositories\PatientAuditLogRepository;
use App\Repositories\PatientRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\PatientValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PatientRepository $repository, PatientValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->resource   = PatientResource::class;
    }

    /**
     * Override the trait's getByWhere (which returns a single `first()` row
     * via successResourceResponse) with a paginated collection so the
     * autocomplete-style callers reading `data.results` get an array.
     *
     * URL: GET /patient/get-by-where?$search=...&$top=10
     */
    public function getByWhere()
    {
        try {
            if (!isset($this->repository)) {
                $this->errorResponse('Repository not defined');
            }

            $result = $this->repository->list();
            $result['results'] = $this->resource::collection(
                $result['results']->map(function ($item) {
                    return new $this->resource($item, true);
                })
            );

            return $this->successResourceCollectionResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if (isset($this->validator)) {
                $this->validate($request, $this->validator->rules(), $this->validator->messages());
            }

            // Auto-generate MRN from code sequence
            $mrn = (new CodeSequenceRepository())->getLatestCodeByLabel('PATIENT');
            if ($mrn === null) {
                $this->errorResponse('MRN sequence not configured. Please seed the PATIENT code sequence.');
            }

            $data = array_merge($request->all(), ['mrn' => $mrn]);

            $result = $this->repository->create($data);

            if (empty($result)) {
                $this->errorResponse('Failed to register patient.');
            }

            // Advance the sequence only after successful save
            (new CodeSequenceRepository())->updateNextSequenceByLabel('PATIENT');

            $this->writeAuditLog($result->id, PatientActionEnum::CREATE, null, $result->only($this->auditableFields()));

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

    /**
     * Override update — capture before/after values for the audit trail.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            if (isset($this->validator)) {
                $this->validate($request, $this->validator->rules(), $this->validator->messages());
            }

            $before = $this->repository->findById($id);
            if (!$before) {
                $this->errorResponse('Patient not found.');
            }
            $oldValues = $before->only($this->auditableFields());

            $this->repository->update($request->all(), $id);

            $result = $this->repository->show($id);
            $this->writeAuditLog($id, PatientActionEnum::UPDATE, $oldValues, $result->only($this->auditableFields()));

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

    /**
     * Override destroy — log the deletion before the record is soft-deleted.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $entity = $this->repository->findById($id);
            if (!$entity) {
                $this->notFoundResponse();
            }

            $oldValues = $entity->only($this->auditableFields());
            $response = $this->repository->delete($id);
            if (!$response) {
                $this->errorResponse();
            }

            $this->writeAuditLog($id, PatientActionEnum::DELETE, $oldValues, null);

            DB::commit();
            return $this->deleteResponse();
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /patient/{id}/audit-log
     */
    public function auditLog(Request $request, $id)
    {
        try {
            $logs = (new PatientAuditLogRepository())->getByPatientId($id);
            $result = $logs->map(function ($log) {
                return [
                    'id'                      => $log->id,
                    'action'                  => $log->action,
                    'action_label'            => PatientActionEnum::label($log->action),
                    'old_values'              => $log->old_values,
                    'new_values'              => $log->new_values,
                    'merged_into_patient_id'  => $log->merged_into_patient_id,
                    'actor_id'                => $log->actor_id,
                    'occurred_at'             => $log->occurred_at,
                    'remarks'                 => $log->remarks,
                ];
            });
            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /patient/merge — merge a duplicate patient record into a survivor.
     * Body: { survivor_patient_id, duplicate_patient_id }
     */
    public function merge(Request $request)
    {
        try {
            $request->validate([
                'survivor_patient_id'  => ['required', 'integer', 'different:duplicate_patient_id', 'exists:patients,id'],
                'duplicate_patient_id' => ['required', 'integer', 'exists:patients,id'],
            ]);

            $survivorId  = (int) $request->input('survivor_patient_id');
            $duplicateId = (int) $request->input('duplicate_patient_id');
            $actorId     = (int) Auth::id();

            $mergeResult = $this->repository->merge($survivorId, $duplicateId, $actorId);

            $reassignedSummary = $mergeResult['reassigned'];
            $totalReassigned   = array_sum($reassignedSummary);

            $this->writeAuditLog(
                $survivorId,
                PatientActionEnum::MERGED,
                null,
                $mergeResult['backfilled'],
                "Merged patient #{$duplicateId} into this record; reassigned {$totalReassigned} related record(s).",
            );
            $this->writeAuditLog(
                $duplicateId,
                PatientActionEnum::MERGED_AWAY,
                null,
                null,
                "Merged into patient #{$survivorId}.",
                $survivorId,
            );

            $response = new $this->resource($mergeResult['survivor'], false);
            return $this->successResponse([
                'patient'    => $response->toArray($request),
                'reassigned' => $reassignedSummary,
                'backfilled' => $mergeResult['backfilled'],
            ]);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /* ---------- Private helpers ---------- */

    /**
     * Fields worth capturing in the audit trail. Excludes internal/system columns.
     */
    private function auditableFields(): array
    {
        return [
            'mrn', 'title', 'first_name', 'middle_name', 'last_name', 'date_of_birth',
            'gender', 'blood_group', 'marital_status', 'email', 'primary_phone',
            'secondary_phone', 'current_address', 'permanent_address',
            'known_allergies', 'chronic_diseases', 'current_medications',
            'insurance_provider', 'insurance_policy_number', 'is_sensitive', 'is_vip', 'status',
        ];
    }

    private function writeAuditLog(int $patientId, string $action, ?array $oldValues, ?array $newValues, ?string $remarks = null, ?int $mergedIntoPatientId = null)
    {
        try {
            PatientAuditLog::create([
                'patient_id'              => $patientId,
                'merged_into_patient_id'  => $mergedIntoPatientId,
                'action'                  => $action,
                'old_values'              => $oldValues,
                'new_values'              => $newValues,
                'actor_id'                => Auth::id(),
                'occurred_at'             => now(),
                'remarks'                 => $remarks,
            ]);
        } catch (\Exception $e) {
            // audit log failures must not block the primary operation
        }
    }
}
