<?php

namespace App\Services\Hr;

use App\Models\LeaveBalance;
use App\Models\LeaveType;

class LeaveBalanceService
{
    public function getOrCreate(int $employeeId, int $leaveTypeId, int $year): LeaveBalance
    {
        $balance = LeaveBalance::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if ($balance) {
            return $balance;
        }

        $leaveType = LeaveType::query()->findOrFail($leaveTypeId);

        return LeaveBalance::query()->create([
            'employee_id'    => $employeeId,
            'leave_type_id'  => $leaveTypeId,
            'year'           => $year,
            'allocated_days' => $leaveType->max_days_per_year,
        ]);
    }

    public function deductForApprovedLeave(int $employeeId, int $leaveTypeId, int $year, float $days): LeaveBalance
    {
        $balance = $this->getOrCreate($employeeId, $leaveTypeId, $year);
        $balance->used_days = round((float) $balance->used_days + $days, 2);
        $balance->save();
        return $balance->fresh();
    }

    public function allocate(int $employeeId, int $leaveTypeId, int $year, float $allocatedDays): LeaveBalance
    {
        $balance = $this->getOrCreate($employeeId, $leaveTypeId, $year);
        $balance->allocated_days = $allocatedDays;
        $balance->save();
        return $balance->fresh();
    }

    public function forEmployee(int $employeeId, int $year)
    {
        $leaveTypes = LeaveType::query()->where('status', 1)->get();

        return $leaveTypes->map(function ($leaveType) use ($employeeId, $year) {
            $balance = $this->getOrCreate($employeeId, $leaveType->id, $year);
            return [
                'leave_type_id'   => $leaveType->id,
                'leave_type_name' => $leaveType->name,
                'allocated_days'  => (float) $balance->allocated_days,
                'used_days'       => (float) $balance->used_days,
                'balance'         => round((float) $balance->allocated_days - (float) $balance->used_days, 2),
            ];
        });
    }
}
