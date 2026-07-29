<?php

namespace App\Repositories;

use App\Models\AtoeAssessment;
use App\Services\ODataService;

class AtoeAssessmentRepository extends BaseRepository
{
    /** @var AtoeAssessment */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['impression', 'plan', 'airway', 'breathing', 'circulation'];

    public function __construct()
    {
        $this->model = new AtoeAssessment();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
