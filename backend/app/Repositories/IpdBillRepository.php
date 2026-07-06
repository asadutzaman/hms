<?php

namespace App\Repositories;

use App\Models\IpdBill;
use App\Services\ODataService;

class IpdBillRepository extends BaseRepository
{
    /**
    * @var IpdBill
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['bill_no', 'bill_status'];

    public function __construct()
    {
        $this->model = new IpdBill();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId): ?IpdBill
    {
        return $this->newQuery()
            ->with(['items', 'payments'])
            ->where('admission_id', $admissionId)
            ->first();
    }

    public function withItemsAndPayments(int $id): IpdBill
    {
        return $this->newQuery()->with(['items', 'payments'])->findOrFail($id);
    }
}
