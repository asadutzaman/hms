<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Repositories\LeaveRequestRepository;
use App\Services\Hr\LeaveBalanceService;
use App\Traits\Controller\RestControllerTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Mirrors GoodsReceiveNoteApprovalController — the generic workflow engine
 * dispatches here on any LeaveRequest workflow action (see
 * WorkflowStepTaskProcessRepository::taskUpdateFields()). On the Approve
 * action, deducts the request's total_days from the employee's leave
 * balance for the leave's year (F-13-03 "balance updated").
 */
class LeaveRequestApprovalController extends Controller
{
    private $repository;

    use RestControllerTrait;

    public function __construct(LeaveRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function workflowProcess($updateFields, $id)
    {
        DB::beginTransaction();
        try {
            if (!isset($this->repository)) {
                $this->errorResponse('Repository not defined');
            }

            if (!empty($updateFields['process_status']) && $updateFields['process_status'] === 'APPROVED') {
                $leaveRequest = $this->repository->findById($id);
                (new LeaveBalanceService())->deductForApprovedLeave(
                    $leaveRequest->employee_id,
                    $leaveRequest->leave_type_id,
                    Carbon::parse($leaveRequest->start_date)->year,
                    (float) $leaveRequest->total_days
                );
                $updateFields['approved_at'] = now();
            }

            $this->repository->update($updateFields, $id);

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
