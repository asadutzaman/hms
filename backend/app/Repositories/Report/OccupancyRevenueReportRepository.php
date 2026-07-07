<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * F-14-03 Occupancy & Revenue Reports — ward-wise current occupancy % plus
 * room-charge revenue for a date range. Occupancy is read directly off
 * beds.bed_status (a live/current-state column, not historical — see
 * project_hms_sprint8_scope memory on why a true historical occupancy-by-
 * date query would need to reconstruct state from admission/discharge
 * dates instead, which this report doesn't attempt).
 */
class OccupancyRevenueReportRepository extends BaseRepository
{
    protected $request;

    public function init()
    {
        $this->request = request();
        return $this;
    }

    public function getReport(): array
    {
        $startDate = $this->request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $this->request->query('end_date', Carbon::now()->toDateString());
        $prefix = DB::connection()->getTablePrefix() ?? '';

        $occupancy = DB::table('beds')
            ->leftJoin('wards', 'wards.id', '=', 'beds.ward_id')
            ->whereNull('beds.deleted_at')
            ->select(
                'beds.ward_id',
                'wards.name as ward_name',
                DB::raw('COUNT(*) as total_beds'),
                DB::raw("SUM(CASE WHEN {$prefix}beds.bed_status = 'occupied' THEN 1 ELSE 0 END) as occupied_beds")
            )
            ->groupBy('beds.ward_id', 'wards.name')
            ->get()
            ->map(function ($row) {
                $row->occupancy_percent = $row->total_beds > 0
                    ? round(($row->occupied_beds / $row->total_beds) * 100, 1)
                    : 0.0;
                return $row;
            });

        $revenue = DB::table('ipd_bill_items')
            ->join('ipd_bills', 'ipd_bills.id', '=', 'ipd_bill_items.ipd_bill_id')
            ->join('ipd_admissions', 'ipd_admissions.id', '=', 'ipd_bills.admission_id')
            ->leftJoin('wards', 'wards.id', '=', 'ipd_admissions.ward_id')
            ->where('ipd_bill_items.item_type', 'room_charge')
            ->whereBetween(DB::raw("DATE({$prefix}ipd_bill_items.created_at)"), [$startDate, $endDate])
            ->whereNull('ipd_bill_items.deleted_at')
            ->select(
                'ipd_admissions.ward_id',
                'wards.name as ward_name',
                DB::raw("SUM({$prefix}ipd_bill_items.line_total) as room_charge_revenue")
            )
            ->groupBy('ipd_admissions.ward_id', 'wards.name')
            ->get()
            ->keyBy('ward_id');

        $results = $occupancy->map(function ($row) use ($revenue) {
            $row->room_charge_revenue = (float) ($revenue->get($row->ward_id)->room_charge_revenue ?? 0);
            return $row;
        });

        return [
            'results' => $results,
            'summary' => [
                'total_beds'      => (int) $results->sum('total_beds'),
                'occupied_beds'   => (int) $results->sum('occupied_beds'),
                'total_revenue'   => (float) $results->sum('room_charge_revenue'),
            ],
            'date_range' => ['start_date' => $startDate, 'end_date' => $endDate],
        ];
    }
}
