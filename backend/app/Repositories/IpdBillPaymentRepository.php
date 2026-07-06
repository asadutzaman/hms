<?php

namespace App\Repositories;

use App\Models\IpdBillPayment;
use App\Services\ODataService;

class IpdBillPaymentRepository extends BaseRepository
{
    /**
    * @var IpdBillPayment
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['payment_method', 'reference_no'];

    public function __construct()
    {
        $this->model = new IpdBillPayment();
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
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();
    }

    public function sumForBill(int $billId): float
    {
        return (float) $this->newQuery()
            ->where('ipd_bill_id', $billId)
            ->where('status', 1)
            ->sum('amount');
    }
}
