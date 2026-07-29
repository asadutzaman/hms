<?php

namespace App\Repositories;

use App\Models\CodeBlueEvent;
use App\Services\ODataService;

class CodeBlueEventRepository extends BaseRepository
{
    /** @var CodeBlueEvent */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['reason', 'location', 'outcome_notes'];

    public function __construct()
    {
        $this->model = new CodeBlueEvent();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
