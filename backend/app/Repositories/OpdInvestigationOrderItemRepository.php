<?php

namespace App\Repositories;

use App\Models\OpdInvestigationOrderItem;
use App\Services\ODataService;

class OpdInvestigationOrderItemRepository extends BaseRepository
{
    /**
     * @var OpdInvestigationOrderItem
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'test_code',
        'test_name',
        'department',
        'result_status',
    ];

    public function __construct()
    {
        $this->model = new OpdInvestigationOrderItem();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forOrder(int $orderId)
    {
        return $this->newQuery()
            ->where('opd_investigation_order_id', $orderId)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();
    }
}
