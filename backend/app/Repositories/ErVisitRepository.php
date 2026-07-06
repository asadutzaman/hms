<?php

namespace App\Repositories;

use App\Models\ErVisit;
use App\Services\ODataService;

class ErVisitRepository extends BaseRepository
{
    /**
    * @var ErVisit
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['er_visit_no', 'chief_complaint'];

    public function __construct()
    {
        $this->model = new ErVisit();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withRelations(int $id): ErVisit
    {
        return $this->newQuery()->with(['patient', 'triages'])->findOrFail($id);
    }

    /**
     * The active ER board — everything not yet at a terminal status,
     * oldest arrival first (longest-waiting patients surface first).
     */
    public function activeBoard()
    {
        return $this->newQuery()
            ->with(['patient', 'triages'])
            ->whereNotIn('er_status', ['discharged', 'admitted', 'lwbs', 'deceased'])
            ->orderBy('arrival_at')
            ->get();
    }
}
