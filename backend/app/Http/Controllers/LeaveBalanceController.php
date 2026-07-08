<?php

namespace App\Http\Controllers;

use App\Services\Hr\LeaveBalanceService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    use TraitRestResponse;

    /** GET /leave-balance/employee/{employeeId}?year= */
    public function forEmployee(Request $request, $employeeId)
    {
        try {
            $year = (int) $request->query('year', now()->year);
            $result = app(LeaveBalanceService::class)->forEmployee((int) $employeeId, $year);
            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /leave-balance/allocate — Body: { employee_id, leave_type_id, year, allocated_days } */
    public function allocate(Request $request)
    {
        try {
            $request->validate([
                'employee_id'    => ['required', 'integer', 'exists:employees,id'],
                'leave_type_id'  => ['required', 'integer', 'exists:leave_types,id'],
                'year'           => ['required', 'integer'],
                'allocated_days' => ['required', 'numeric', 'min:0'],
            ]);

            $balance = app(LeaveBalanceService::class)->allocate(
                (int) $request->input('employee_id'),
                (int) $request->input('leave_type_id'),
                (int) $request->input('year'),
                (float) $request->input('allocated_days')
            );

            return $this->successResponse($balance);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
