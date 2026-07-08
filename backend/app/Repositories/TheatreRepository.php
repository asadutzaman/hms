<?php

namespace App\Repositories;

use App\Models\Theatre;
use App\Services\ODataService;

class TheatreRepository extends BaseRepository
{
    /**
    * @var Theatre
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name', 'description'];

    public function __construct()
    {
       $this->model         = new Theatre();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
