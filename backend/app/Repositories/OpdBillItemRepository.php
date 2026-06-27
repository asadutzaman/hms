<?php

namespace App\Repositories;

use App\Models\OpdBillItem;
use App\Services\ODataService;

class OpdBillItemRepository extends BaseRepository
{
    /**
     * @var OpdBillItem
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'description',
        'code',
        'item_type',
    ];

    public function __construct()
    {
        $this->model = new OpdBillItem();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forBill(int $billId)
    {
        return $this->newQuery()
            ->where('opd_bill_id', $billId)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();
    }
}
