<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * F-14-07 Pharmacy Sales Analytics — top drugs by dispensed volume, with
 * near-expiry stock highlighted. There is no per-dispense sale-price
 * column anywhere in the schema (opd_prescription_dispense_items only
 * carries dispensed_quantity) — "sales value" here is an ESTIMATE, the
 * dispensed quantity multiplied by that drug's average item_stocks
 * unit_price (the purchase/batch price, the only price this app tracks
 * for drugs). Flagged clearly in the response key name
 * (`estimated_sales_value`) rather than presented as exact billed revenue.
 */
class PharmacySalesAnalyticsRepository extends BaseRepository
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
        $limit = (int) $this->request->query('limit', 20);
        $prefix = DB::connection()->getTablePrefix() ?? '';

        $topDrugs = DB::table('opd_prescription_dispense_items')
            ->join('drugs', 'drugs.id', '=', 'opd_prescription_dispense_items.drug_id')
            ->leftJoin('item_stocks', 'item_stocks.item_id', '=', 'drugs.item_id')
            ->whereBetween(DB::raw("DATE({$prefix}opd_prescription_dispense_items.created_at)"), [$startDate, $endDate])
            ->whereNull('opd_prescription_dispense_items.deleted_at')
            ->select(
                'drugs.id as drug_id',
                'drugs.generic_name',
                'drugs.brand_name',
                DB::raw("SUM({$prefix}opd_prescription_dispense_items.dispensed_quantity) as total_quantity"),
                DB::raw("COUNT(DISTINCT {$prefix}opd_prescription_dispense_items.opd_prescription_dispense_id) as dispense_count"),
                DB::raw("AVG({$prefix}item_stocks.unit_price) as avg_unit_price"),
                DB::raw("SUM({$prefix}opd_prescription_dispense_items.dispensed_quantity) * AVG({$prefix}item_stocks.unit_price) as estimated_sales_value")
            )
            ->groupBy('drugs.id', 'drugs.generic_name', 'drugs.brand_name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        $nearExpiry = DB::table('item_stocks')
            ->join('drugs', 'drugs.item_id', '=', 'item_stocks.item_id')
            ->whereNull('item_stocks.deleted_at')
            ->where('item_stocks.balance_quantity', '>', 0)
            ->whereNotNull('item_stocks.expire_date')
            ->whereBetween('item_stocks.expire_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(90)->toDateString()])
            ->select(
                'drugs.id as drug_id',
                'drugs.generic_name',
                'drugs.brand_name',
                'item_stocks.balance_quantity',
                'item_stocks.expire_date'
            )
            ->orderBy('item_stocks.expire_date')
            ->limit(50)
            ->get();

        // Slow-moving: drugs with stock but near-zero dispense volume in the range.
        $dispensedDrugIds = DB::table('opd_prescription_dispense_items')
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('drug_id');

        $slowMoving = DB::table('drugs')
            ->join('item_stocks', 'item_stocks.item_id', '=', 'drugs.item_id')
            ->whereNull('item_stocks.deleted_at')
            ->where('item_stocks.balance_quantity', '>', 0)
            ->whereNotIn('drugs.id', $dispensedDrugIds->isEmpty() ? [0] : $dispensedDrugIds)
            ->select('drugs.id as drug_id', 'drugs.generic_name', 'drugs.brand_name', DB::raw("SUM({$prefix}item_stocks.balance_quantity) as balance_quantity"))
            ->groupBy('drugs.id', 'drugs.generic_name', 'drugs.brand_name')
            ->orderByDesc('balance_quantity')
            ->limit(20)
            ->get();

        return [
            'top_drugs'   => $topDrugs,
            'near_expiry' => $nearExpiry,
            'slow_moving' => $slowMoving,
            'date_range'  => ['start_date' => $startDate, 'end_date' => $endDate],
        ];
    }
}
