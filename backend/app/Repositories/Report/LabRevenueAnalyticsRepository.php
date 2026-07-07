<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * F-14-08 Lab Revenue Analytics — test-wise order volume + revenue
 * (price_snapshot on lab_order_items, the only place lab charges are
 * recorded — see project_hms_sprint8_scope memory), plus TAT compliance
 * (ordered_at -> lab_results.verified_at, since lab_orders itself has no
 * completed_at/reported_at column).
 */
class LabRevenueAnalyticsRepository extends BaseRepository
{
    protected $request;

    public function init()
    {
        $this->request = request();
        return $this;
    }

    public function getReport(): array
    {
        $startDate = $this->request->query('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $this->request->query('end_date', Carbon::now()->toDateString());
        $tatTargetHours = (float) $this->request->query('tat_target_hours', 24);

        $prefix = DB::connection()->getTablePrefix() ?? '';

        $testWise = DB::table('lab_order_items')
            ->join('lab_orders', 'lab_orders.id', '=', 'lab_order_items.lab_order_id')
            ->leftJoin('lab_tests', 'lab_tests.id', '=', 'lab_order_items.lab_test_id')
            ->whereBetween(DB::raw("DATE({$prefix}lab_orders.ordered_at)"), [$startDate, $endDate])
            ->whereNull('lab_order_items.deleted_at')
            ->where('lab_order_items.item_status', '!=', 'cancelled')
            ->select(
                'lab_order_items.lab_test_id',
                DB::raw("COALESCE({$prefix}lab_tests.name, {$prefix}lab_order_items.test_name_snapshot) as test_name"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw("SUM({$prefix}lab_order_items.price_snapshot) as revenue")
            )
            ->groupBy('lab_order_items.lab_test_id', 'lab_tests.name', 'lab_order_items.test_name_snapshot')
            ->orderByDesc('revenue')
            ->get();

        $tatRows = DB::table('lab_orders')
            ->join('lab_order_items', 'lab_order_items.lab_order_id', '=', 'lab_orders.id')
            ->join('lab_results', 'lab_results.lab_order_item_id', '=', 'lab_order_items.id')
            ->whereBetween(DB::raw("DATE({$prefix}lab_orders.ordered_at)"), [$startDate, $endDate])
            ->whereNotNull('lab_results.verified_at')
            ->select(
                'lab_orders.id as lab_order_id',
                'lab_orders.ordered_at',
                DB::raw("MAX({$prefix}lab_results.verified_at) as verified_at")
            )
            ->groupBy('lab_orders.id', 'lab_orders.ordered_at')
            ->get()
            ->map(function ($row) {
                $orderedAt = Carbon::parse($row->ordered_at);
                $verifiedAt = Carbon::parse($row->verified_at);
                $row->tat_hours = round($orderedAt->diffInMinutes($verifiedAt) / 60, 1);
                return $row;
            });

        $withinTarget = $tatRows->where('tat_hours', '<=', $tatTargetHours)->count();
        $totalCompleted = $tatRows->count();
        $tatComplianceRate = $totalCompleted > 0 ? round(($withinTarget / $totalCompleted) * 100, 1) : 0.0;

        return [
            'test_wise' => $testWise,
            'tat_summary' => [
                'target_hours'        => $tatTargetHours,
                'total_completed'     => $totalCompleted,
                'within_target_count' => $withinTarget,
                'compliance_rate'     => $tatComplianceRate,
                'average_tat_hours'   => $totalCompleted > 0 ? round($tatRows->avg('tat_hours'), 1) : 0.0,
            ],
            'summary' => [
                'total_orders'  => (int) $testWise->sum('order_count'),
                'total_revenue' => (float) $testWise->sum('revenue'),
            ],
            'date_range' => ['start_date' => $startDate, 'end_date' => $endDate],
        ];
    }
}
