<?php

namespace App\Repositories;

use App\Models\IpdFluidBalance;
use App\Services\ODataService;
use Carbon\Carbon;

class IpdFluidBalanceRepository extends BaseRepository
{
    /**
    * @var IpdFluidBalance
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['category', 'notes'];

    public function __construct()
    {
        $this->model = new IpdFluidBalance();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId)
    {
        return $this->newQuery()
            ->where('admission_id', $admissionId)
            ->orderByDesc('recorded_at')
            ->get();
    }

    /**
     * Daily intake/output totals + net balance for an admission, most
     * recent day first — the shape the fluid-balance chart/table consumes.
     */
    public function dailySummary(int $admissionId): array
    {
        $rows = $this->forAdmission($admissionId);

        $byDate = [];
        foreach ($rows as $row) {
            $date = Carbon::parse($row->recorded_at)->toDateString();
            if (!isset($byDate[$date])) {
                $byDate[$date] = ['date' => $date, 'intake' => 0.0, 'output' => 0.0];
            }
            $byDate[$date][$row->balance_type] += (float) $row->amount_ml;
        }

        $summary = array_values($byDate);
        foreach ($summary as &$day) {
            $day['balance'] = round($day['intake'] - $day['output'], 2);
        }
        usort($summary, fn ($a, $b) => strcmp($b['date'], $a['date']));

        return $summary;
    }
}
