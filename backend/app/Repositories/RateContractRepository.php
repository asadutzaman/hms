<?php

namespace App\Repositories;

use App\Models\RateContract;
use App\Services\ODataService;

class RateContractRepository extends BaseRepository
{
    /**
    * @var RateContract
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['contract_status'];

    public function __construct()
    {
        $this->model = new RateContract();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
