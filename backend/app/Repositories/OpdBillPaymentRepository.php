<?php

namespace App\Repositories;

use App\Models\OpdBillPayment;
use App\Services\ODataService;

class OpdBillPaymentRepository extends BaseRepository
{
    /**
     * @var OpdBillPayment
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'payment_method',
        'transaction_no',
        'reference_no',
    ];

    public function __construct()
    {
        $this->model = new OpdBillPayment();
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
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();
    }

    public function sumForBill(int $billId): float
    {
        return (float) $this->newQuery()
            ->where('opd_bill_id', $billId)
            ->where('status', 1)
            ->sum('amount');
    }
}
