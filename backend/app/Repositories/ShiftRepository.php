<?php

namespace App\Repositories;

use App\Models\Shift;
use App\Services\ODataService;

class ShiftRepository extends BaseRepository
{
    /**
    * @var Shift
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name', 'description'];

    public function __construct()
    {
       $this->model         = new Shift();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
