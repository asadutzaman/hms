<?php

namespace App\Repositories;

use App\Models\OpdProcedure;
use App\Services\ODataService;

class OpdProcedureRepository extends BaseRepository
{
    /**
    * @var OpdProcedure
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new OpdProcedure();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
