<?php

namespace App\Repositories;

use App\Models\RadiologyOrderItem;
use App\Services\ODataService;

class RadiologyOrderItemRepository extends BaseRepository
{
    /**
    * @var RadiologyOrderItem
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['test_name_snapshot'];

    public function __construct()
    {
        $this->model = new RadiologyOrderItem();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withReport(int $id): RadiologyOrderItem
    {
        return $this->newQuery()->with(['report', 'order.patient'])->findOrFail($id);
    }
}
