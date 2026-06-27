<?php

namespace App\Repositories;

use App\Models\LabTest;
use App\Services\ODataService;

class LabTestRepository extends BaseRepository
{
    /**
    * @var LabTest
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new LabTest();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
