<?php

namespace App\Repositories;

use App\Models\Bed;
use App\Services\ODataService;

class BedRepository extends BaseRepository
{
    /**
    * @var Bed
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['bed_number', 'bed_type'];

    public function __construct()
    {
       $this->model         = new Bed();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /**
     * Bed occupancy summary grouped by ward, for the IPD bed-board dashboard.
     * Buckets cover all five bed_status values (vacant, occupied, reserved,
     * cleaning, maintenance) — see BedValidator for the canonical list.
     */
    public function occupancyDashboard(): array
    {
        $beds = $this->newQuery()->with('ward')->where('status', 1)->get();

        $statuses = ['vacant', 'occupied', 'reserved', 'cleaning', 'maintenance'];
        $emptyCounts = array_fill_keys($statuses, 0);

        $wards = [];
        foreach ($beds as $bed) {
            $wardId = $bed->ward_id;
            if (!isset($wards[$wardId])) {
                $wards[$wardId] = array_merge(
                    [
                        'ward_id'   => $wardId,
                        'ward_name' => optional($bed->ward)->name,
                        'total'     => 0,
                    ],
                    $emptyCounts,
                );
            }

            $wards[$wardId]['total']++;
            $status = in_array($bed->bed_status, $statuses, true) ? $bed->bed_status : 'vacant';
            $wards[$wardId][$status]++;
        }

        $wards = array_values($wards);

        $summary = array_merge(['total' => $beds->count()], $emptyCounts);
        foreach ($statuses as $status) {
            $summary[$status] = $beds->where('bed_status', $status)->count();
        }

        return [
            'summary' => $summary,
            'wards'   => $wards,
        ];
    }

    /**
     * Full bed list (with ward) for the visual bed-board — each bed tile
     * needs ward grouping plus enough context (type/rate/current admission)
     * to be actionable from the dashboard.
     */
    public function boardBeds()
    {
        return $this->newQuery()
            ->with(['ward', 'admissions' => function ($q) {
                $q->where('admission_status', 'admitted')->with('patient')->latest('id')->limit(1);
            }])
            ->where('status', 1)
            ->get();
    }
}
