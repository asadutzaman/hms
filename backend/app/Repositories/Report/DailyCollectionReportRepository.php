<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyCollectionReportRepository extends BaseRepository
{
    protected $request;

    public function init()
    {
        $this->request = request();
        return $this;
    }

    protected function buildQuery()
    {
        $startDate = $this->request->query('start_date', Carbon::today()->toDateString());
        $endDate = $this->request->query('end_date', Carbon::today()->toDateString());
        $departmentId = $this->request->query('department_id');
        $doctorId = $this->request->query('doctor_id');
        $prefix = DB::connection()->getTablePrefix() ?? '';

        return DB::table('opd_bill_payments')
            ->join('opd_bills', 'opd_bills.id', '=', 'opd_bill_payments.opd_bill_id')
            ->join('opd_visits', 'opd_visits.id', '=', 'opd_bills.opd_visit_id')
            ->leftJoin('departments', 'departments.id', '=', 'opd_visits.department_id')
            ->leftJoin('employees', 'employees.id', '=', 'opd_visits.doctor_id')
            ->whereNull('opd_bill_payments.deleted_at')
            ->whereBetween(DB::raw("DATE({$prefix}opd_bill_payments.paid_at)"), [$startDate, $endDate])
            ->when(!empty($departmentId), function ($query) use ($departmentId) {
                $query->where('opd_visits.department_id', $departmentId);
            })
            ->when(!empty($doctorId), function ($query) use ($doctorId) {
                $query->where('opd_visits.doctor_id', $doctorId);
            })
            ->select(
                DB::raw("DATE({$prefix}opd_bill_payments.paid_at) as collection_date"),
                'opd_bill_payments.payment_method',
                'opd_visits.department_id',
                'departments.name as department_name',
                'opd_visits.doctor_id',
                'employees.name_en as doctor_name',
                DB::raw("SUM({$prefix}opd_bill_payments.amount) as total_amount"),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy(
                DB::raw("DATE({$prefix}opd_bill_payments.paid_at)"),
                'opd_bill_payments.payment_method',
                'opd_visits.department_id',
                'departments.name',
                'opd_visits.doctor_id',
                'employees.name_en'
            )
            ->orderBy('collection_date', 'desc');
    }

    public function getDailyCollectionList()
    {
        $rows = $this->buildQuery()->get();

        $summary = [
            'total_amount'       => (float) $rows->sum('total_amount'),
            'total_transactions' => (int) $rows->sum('transaction_count'),
            'by_payment_method'  => $rows->groupBy('payment_method')->map(function ($group) {
                return (float) $group->sum('total_amount');
            }),
        ];

        return [
            'results' => $rows,
            'summary' => $summary,
        ];
    }

    public function getDailyCollectionExport()
    {
        return [
            'results' => $this->buildQuery()->get(),
        ];
    }
}
