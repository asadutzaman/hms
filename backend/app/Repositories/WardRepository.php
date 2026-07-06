<?php

namespace App\Repositories;

use App\Models\Ward;
use App\Services\ODataService;

class WardRepository extends BaseRepository
{
    /**
    * @var Ward
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name', 'description'];

    public function __construct()
    {
       $this->model         = new Ward();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
