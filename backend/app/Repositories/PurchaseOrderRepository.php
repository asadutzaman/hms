<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use App\Services\ODataService;

class PurchaseOrderRepository extends BaseRepository
{
    /**
    * @var PurchaseOrder
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['po_number', 'branch_id', 'status'];

    public function __construct()
    {
        $this->model = new PurchaseOrder();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    // CHECK PO NUMBER UNIQUE
    public function checkPoNumberUnique($poNumber, $id = null)
    {
        return $this->newQuery()
            ->where('po_number', $poNumber)
            ->when((isset($id)), function ($query) use ($id) {
                return $query->whereNot('id', $id);
            })
            ->count();
    }
}
