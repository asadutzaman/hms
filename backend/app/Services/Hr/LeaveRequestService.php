<?php

namespace App\Services\Hr;

use App\Exceptions\ApiException;
use App\Models\LeaveRequest;
use App\Repositories\LeaveRequestRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * F-13-03 Leave Management. Leave requests are created straight into
 * process_status='SUBMITTED' (no separate draft-then-submit step — there's
 * no meaningful reason for an employee to save a leave request as a draft
 * in this simple domain) and immediately enter the generic workflow
 * engine's Approval step (same 2-step wiring as GoodsReceiveNote — see
 * project_hms_workflow_engine_and_scaffolding_quirks memory). Balance
 * deduction happens in LeaveRequestApprovalController::workflowProcess()
 * when the workflow engine calls back on APPROVED, not here.
 */
class LeaveRequestService
{
    protected LeaveRequestRepository $repository;

    public function __construct(LeaveRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function apply(array $data, int $actorId): LeaveRequest
    {
        return DB::transaction(function () use ($data, $actorId) {
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

            if ($totalDays <= 0) {
                throw new ApiException('End date must be on or after the start date.', 422);
            }
            if ($this->repository->hasOverlap((int) $data['employee_id'], $startDate, $endDate)) {
                throw new ApiException('This employee already has a leave request overlapping these dates.', 422);
            }

            return LeaveRequest::query()->create([
                'request_no'     => $this->generateRequestNo(now()->toDateString()),
                'employee_id'    => $data['employee_id'],
                'leave_type_id'  => $data['leave_type_id'],
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'total_days'     => $totalDays,
                'reason'         => $data['reason'] ?? null,
                'process_status' => 'SUBMITTED',
                'applied_by'     => $actorId,
                'created_by'     => $actorId,
            ])->fresh(['employee', 'leaveType']);
        });
    }

    protected function generateRequestNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'LEAVE_REQUEST')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'LEAVE_REQUEST',
                    'prefix'        => 'LV',
                    'separator'     => '-',
                    'next_sequence' => 2,
                    'sequence_date' => $dateYmd,
                    'status'        => 1,
                    'sort_order'    => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')->where('id', $row->id)->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "LV-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
