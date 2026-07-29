<?php

namespace App\Repositories;

use App\Models\OrderSet;
use App\Services\ODataService;

class OrderSetRepository extends BaseRepository
{
    /** @var OrderSet */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name', 'category', 'description'];

    public function __construct()
    {
        $this->model = new OrderSet();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
