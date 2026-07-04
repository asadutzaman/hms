<?php

namespace App\Repositories;

use App\Models\PurchaseOrderItem;
use App\Services\ODataService;

class PurchaseOrderItemRepository extends BaseRepository
{
    /**
    * @var PurchaseOrderItem
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['item_id'];

    public function __construct()
    {
        $this->model = new PurchaseOrderItem();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function deletePoItemByIds($purchaseOrderId, $poItemIds)
    {
        return $this->newQuery()
            ->where('purchase_order_id', $purchaseOrderId)
            ->whereNotIn('id', $poItemIds)
            ->delete();
    }
}
