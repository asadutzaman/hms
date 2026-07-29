<?php

namespace App\Repositories;

use App\Models\DischargeChecklist;
use App\Services\ODataService;

class DischargeChecklistRepository extends BaseRepository
{
    /** @var DischargeChecklist */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['state'];

    public function __construct()
    {
        $this->model = new DischargeChecklist();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
