<?php

namespace App\Repositories;

use App\Models\LeaveType;
use App\Services\ODataService;

class LeaveTypeRepository extends BaseRepository
{
    /**
    * @var LeaveType
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name', 'description'];

    public function __construct()
    {
       $this->model         = new LeaveType();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
