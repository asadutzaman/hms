<?php

namespace App\Repositories;

use App\Models\LeaveRequest;

class LeaveRequestRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = ['request_no', 'process_status'];

    public function __construct(LeaveRequest $model)
    {
        $this->model = $model;
    }

    public function withRelations(int $id)
    {
        return $this->newQuery()->with(['employee', 'leaveType'])->find($id);
    }

    public function forEmployee(int $employeeId)
    {
        return $this->newQuery()
            ->with(['leaveType'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();
    }

    /** Overlap check — same employee cannot have two active (non-rejected/non-cancelled) leave requests over the same dates. */
    public function hasOverlap(int $employeeId, string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $query = $this->newQuery()
            ->where('employee_id', $employeeId)
            ->whereNotIn('process_status', ['REJECTED'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
