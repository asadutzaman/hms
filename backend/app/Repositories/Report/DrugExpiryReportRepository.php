<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use App\Repositories\ItemStockRepository;
use Carbon\Carbon;

class DrugExpiryReportRepository extends BaseRepository
{
    protected $request;

    public function init()
    {
        $this->request = request();
        return $this;
    }

    protected function buildExpiryQuery(int $days)
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays($days);

        return (new ItemStockRepository())
            ->newQuery()
            ->select('item_stocks.*', 'drugs.id as drug_id', 'drugs.generic_name', 'drugs.brand_name', 'drugs.dosage_form')
            ->join('drugs', 'drugs.item_id', '=', 'item_stocks.item_id')
            ->with(['branchInfo:id,name', 'itemInfo:id,name_en,name_bn,code', 'shelveInfo:id,name'])
            ->where('item_stocks.balance_quantity', '>', 0)
            ->whereNotNull('item_stocks.expire_date')
            ->whereBetween('item_stocks.expire_date', [$today->toDateString(), $endDate->toDateString()])
            ->orderBy('item_stocks.expire_date', 'asc');
    }

    protected function mapResults($stocks)
    {
        $today = Carbon::today();

        return $stocks->map(function ($stock) use ($today) {
            $expireDate = Carbon::parse($stock->expire_date);
            $daysLeft = (int) $today->diffInDays($expireDate, false);
            $bucket = $daysLeft <= 30 ? 30 : ($daysLeft <= 60 ? 60 : 90);

            return [
                'id'               => $stock->id,
                'item_id'          => $stock->item_id,
                'drug_id'          => $stock->drug_id,
                'generic_name'     => $stock->generic_name,
                'brand_name'       => $stock->brand_name,
                'dosage_form'      => $stock->dosage_form,
                'item_code'        => optional($stock->itemInfo)->code,
                'item_name_en'     => optional($stock->itemInfo)->name_en,
                'item_name_bn'     => optional($stock->itemInfo)->name_bn,
                'branch_name'      => optional($stock->branchInfo)->name,
                'shelve_name'      => optional($stock->shelveInfo)->name,
                'balance_quantity' => $stock->balance_quantity,
                'expire_date'      => $stock->expire_date ? $stock->expire_date->format('Y-m-d') : null,
                'days_left'        => $daysLeft,
                'bucket'           => $bucket,
            ];
        })->values();
    }

    public function getExpiryList()
    {
        $days = (int) ($this->request->query('days', 90));
        $days = $days > 0 ? $days : 90;

        $stocks = $this->buildExpiryQuery($days)->get();
        $results = $this->mapResults($stocks);

        $summary = [
            '30' => $results->where('bucket', 30)->count(),
            '60' => $results->where('bucket', 60)->count(),
            '90' => $results->where('bucket', 90)->count(),
        ];

        return [
            'results' => $results,
            'summary' => $summary,
        ];
    }

    public function getExpiryListExport()
    {
        $days = (int) ($this->request->query('days', 90));
        $days = $days > 0 ? $days : 90;

        $stocks = $this->buildExpiryQuery($days)->get();
        $results = $this->mapResults($stocks);

        return [
            'results' => $results,
        ];
    }
}
