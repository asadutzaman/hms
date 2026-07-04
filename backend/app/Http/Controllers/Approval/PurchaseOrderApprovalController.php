<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseOrderResource;
use App\Repositories\PurchaseOrderRepository;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseOrderApprovalController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PurchaseOrderRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = PurchaseOrderResource::class;
    }

    /**
     * Status transitions only — no stock movement here. Stock only moves
     * when a GRN is received against this PO.
     */
    public function workflowProcess($updateFields, $id)
    {
        DB::beginTransaction();
        try {
            if (!isset($this->repository)) {
                $this->errorResponse('Repository not defined');
            }

            $poStatusMap = [
                'APPROVED' => 'approved',
                'REJECTED' => 'cancelled',
            ];

            $data = $updateFields;
            if (!empty($updateFields['process_status']) && isset($poStatusMap[$updateFields['process_status']])) {
                $data['po_status'] = $poStatusMap[$updateFields['process_status']];
            }
            if (!empty($updateFields['process_status']) && $updateFields['process_status'] == 'APPROVED') {
                $sessionService = (new SessionService())->init();
                $userData = $sessionService->getUserData();
                $data['approved_by'] = $userData['id'] ?? null;
                $data['approved_at'] = now();
            }

            $this->repository->update($data, $id);

            DB::commit();
            $response = 'Workflow process successfully';
            return $this->successResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
            return false;
        }
    }
}
