<?php

namespace App\Repositories;

use App\Models\ErTriage;
use App\Services\ODataService;

class ErTriageRepository extends BaseRepository
{
    /**
    * @var ErTriage
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['notes'];

    public function __construct()
    {
        $this->model = new ErTriage();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forVisit(int $erVisitId)
    {
        return $this->newQuery()->where('er_visit_id', $erVisitId)->orderByDesc('triaged_at')->get();
    }
}
