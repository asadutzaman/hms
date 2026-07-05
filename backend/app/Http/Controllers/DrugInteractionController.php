<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Models\OpdPrescriptionItem;
use App\Models\OpdVisit;
use App\Validators\DrugInteractionValidator;
use App\Repositories\DrugInteractionRepository;
use App\Http\Resources\DrugInteractionResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DrugInteractionController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(DrugInteractionRepository $repository, DrugInteractionValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = DrugInteractionResource::class;
    }

    /**
     * A pair is stored once with the smaller id first, so lookups don't need
     * to check both orderings against the unique index.
     */
    private function normalizePair(array $data): array
    {
        if (!empty($data['drug_a_id']) && !empty($data['drug_b_id']) && (int) $data['drug_a_id'] > (int) $data['drug_b_id']) {
            [$data['drug_a_id'], $data['drug_b_id']] = [$data['drug_b_id'], $data['drug_a_id']];
        }
        return $data;
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages());
            $result = $this->repository->create($this->normalizePair($request->all()));
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages());
            $result = $this->repository->update($this->normalizePair($request->all()), $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /drug-interaction/check
     * body: { drug_ids: [1,2], patient_id?: 5 }
     * When patient_id is given, also checks the new drug(s) against every
     * drug already prescribed in that patient's other non-completed visits,
     * so a warning surfaces even if the conflicting drug isn't in today's
     * draft prescription.
     */
    public function check(Request $request)
    {
        try {
            $drugIds = $request->input('drug_ids', []);
            $patientId = $request->input('patient_id');

            if (!empty($patientId)) {
                $activeVisitIds = OpdVisit::query()
                    ->where('patient_id', $patientId)
                    ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
                    ->pluck('id');

                $existingDrugIds = OpdPrescriptionItem::query()
                    ->whereIn('opd_visit_id', $activeVisitIds)
                    ->whereNotNull('drug_id')
                    ->pluck('drug_id')
                    ->toArray();

                $drugIds = array_merge($drugIds, $existingDrugIds);
            }

            $interactions = $this->repository->checkForDrugs($drugIds);
            return $this->successResponse(['interactions' => $interactions]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
