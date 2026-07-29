<?php

namespace App\Repositories;

use App\Models\Bleep;
use App\Services\ODataService;

class BleepRepository extends BaseRepository
{
    /** @var Bleep */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['message', 'callback'];

    public function __construct()
    {
        $this->model = new Bleep();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
