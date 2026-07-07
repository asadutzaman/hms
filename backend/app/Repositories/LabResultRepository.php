<?php

namespace App\Repositories;

use App\Models\LabResult;
use App\Services\ODataService;

class LabResultRepository extends BaseRepository
{
    /**
    * @var LabResult
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['parameter_name_snapshot'];

    public function __construct()
    {
        $this->model = new LabResult();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forOrderItem(int $orderItemId)
    {
        return $this->newQuery()->where('lab_order_item_id', $orderItemId)->get();
    }
}
