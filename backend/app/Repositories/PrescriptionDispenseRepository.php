<?php

namespace App\Repositories;

use App\Models\PrescriptionDispense;
use App\Services\ODataService;

class PrescriptionDispenseRepository extends BaseRepository
{
    /**
    * @var PrescriptionDispense
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new PrescriptionDispense();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
