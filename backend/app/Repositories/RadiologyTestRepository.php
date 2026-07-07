<?php

namespace App\Repositories;

use App\Models\RadiologyTest;
use App\Services\ODataService;

class RadiologyTestRepository extends BaseRepository
{
    /**
    * @var RadiologyTest
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['code', 'name', 'body_part'];

    public function __construct()
    {
        $this->model = new RadiologyTest();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
