<?php

namespace App\Repositories;

use App\Models\IpdAdvancePayment;
use App\Services\ODataService;

class IpdAdvancePaymentRepository extends BaseRepository
{
    /**
    * @var IpdAdvancePayment
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['payment_method', 'reference_no'];

    public function __construct()
    {
        $this->model = new IpdAdvancePayment();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId)
    {
        return $this->newQuery()
            ->where('admission_id', $admissionId)
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Advances with unapplied balance remaining (amount &gt; applied_amount),
     * oldest first — FIFO application order.
     */
    public function unappliedForAdmission(int $admissionId)
    {
        return $this->newQuery()
            ->where('admission_id', $admissionId)
            ->whereColumn('applied_amount', '<', 'amount')
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();
    }

    public function sumUnappliedForAdmission(int $admissionId): float
    {
        $rows = $this->unappliedForAdmission($admissionId);
        return round((float) $rows->sum(fn ($row) => (float) $row->amount - (float) $row->applied_amount), 2);
    }
}
