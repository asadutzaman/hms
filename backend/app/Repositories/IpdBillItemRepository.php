<?php

namespace App\Repositories;

use App\Models\IpdBillItem;
use App\Services\ODataService;

class IpdBillItemRepository extends BaseRepository
{
    /**
    * @var IpdBillItem
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['description', 'item_type'];

    public function __construct()
    {
        $this->model = new IpdBillItem();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forBill(int $billId)
    {
        return $this->newQuery()
            ->where('ipd_bill_id', $billId)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();
    }

    public function deleteByType(int $billId, string $itemType): int
    {
        return $this->newQuery()
            ->where('ipd_bill_id', $billId)
            ->where('item_type', $itemType)
            ->delete();
    }
}
