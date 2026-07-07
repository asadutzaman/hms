<?php

namespace App\Repositories;

use App\Models\BillRefund;
use App\Services\ODataService;

class BillRefundRepository extends BaseRepository
{
    /**
    * @var BillRefund
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['refund_no', 'reason'];

    public function __construct()
    {
        $this->model = new BillRefund();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forBillable(string $billableType, int $billableId)
    {
        return $this->newQuery()
            ->where('billable_type', $billableType)
            ->where('billable_id', $billableId)
            ->orderByDesc('requested_at')
            ->get();
    }

    public function pending()
    {
        return $this->newQuery()->where('refund_status', 'pending_approval')->orderBy('requested_at')->get();
    }
}
