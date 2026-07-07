<?php

namespace App\Repositories;

use App\Models\LabOrderItem;
use App\Services\ODataService;

class LabOrderItemRepository extends BaseRepository
{
    /**
    * @var LabOrderItem
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['test_name_snapshot'];

    public function __construct()
    {
        $this->model = new LabOrderItem();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withResults(int $id): LabOrderItem
    {
        return $this->newQuery()->with(['results', 'order'])->findOrFail($id);
    }
}
