<?php

namespace App\Repositories;

use App\Models\PrescriptionDispenseItem;
use App\Services\ODataService;

class PrescriptionDispenseItemRepository extends BaseRepository
{
    /**
    * @var PrescriptionDispenseItem
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new PrescriptionDispenseItem();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
