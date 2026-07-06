<?php

namespace App\Repositories;

use App\Models\IpdMedicationAdministration;
use App\Services\ODataService;

class IpdMedicationAdministrationRepository extends BaseRepository
{
    /**
    * @var IpdMedicationAdministration
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['reason', 'notes'];

    public function __construct()
    {
        $this->model = new IpdMedicationAdministration();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forOrder(int $orderId)
    {
        return $this->newQuery()->where('order_id', $orderId)->orderBy('scheduled_at')->get();
    }

    /**
     * All due/upcoming administration slots for an admission's active
     * orders, for the "due now" MAR worklist view.
     */
    public function forAdmission(int $admissionId)
    {
        return $this->newQuery()
            ->whereHas('order', function ($q) use ($admissionId) {
                $q->where('admission_id', $admissionId);
            })
            ->with('order')
            ->orderBy('scheduled_at')
            ->get();
    }
}
