<?php

namespace App\Repositories;

use App\Models\ClinicalJob;
use App\Services\ODataService;

class ClinicalJobRepository extends BaseRepository
{
    /** @var ClinicalJob */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description', 'job_type'];

    public function __construct()
    {
        $this->model = new ClinicalJob();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
