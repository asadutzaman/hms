<?php

namespace App\Http\Controllers;

use App\Services\SessionService;
use App\Validators\RateContractValidator;
use App\Repositories\RateContractRepository;
use App\Http\Resources\RateContractResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class RateContractController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(RateContractRepository $repository, RateContractValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = RateContractResource::class;
    }

    /**
     * POST /rate-contract/{id}/submit — move a draft contract to
     * pending_approval so a reviewer can act on it.
     */
    public function submit($id)
    {
        return $this->transition($id, 'SUBMITTED', 'pending_approval');
    }

    /**
     * POST /rate-contract/{id}/approve
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $contract = $this->repository->findById($id);
            if (!$contract) {
                $this->notFoundResponse();
            }

            $sessionService = (new SessionService())->init();
            $userData = $sessionService->getUserData();

            $this->repository->update([
                'process_status'  => 'APPROVED',
                'contract_status' => 'active',
                'approved_by'     => $userData['id'] ?? null,
                'approved_at'     => now(),
            ], $id);

            DB::commit();
            $result = $this->repository->show($id);
            return $this->successResourceResponse(new $this->resource($result, false));
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /rate-contract/{id}/reject
     */
    public function reject($id)
    {
        return $this->transition($id, 'REJECTED', 'cancelled');
    }

    private function transition($id, string $processStatus, string $contractStatus)
    {
        DB::beginTransaction();
        try {
            $contract = $this->repository->findById($id);
            if (!$contract) {
                $this->notFoundResponse();
            }

            $this->repository->update([
                'process_status'  => $processStatus,
                'contract_status' => $contractStatus,
            ], $id);

            DB::commit();
            $result = $this->repository->show($id);
            return $this->successResourceResponse(new $this->resource($result, false));
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
