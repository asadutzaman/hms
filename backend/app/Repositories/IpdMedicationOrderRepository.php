<?php

namespace App\Repositories;

use App\Models\IpdMedicationOrder;
use App\Services\ODataService;

class IpdMedicationOrderRepository extends BaseRepository
{
    /**
    * @var IpdMedicationOrder
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['drug_name', 'generic_name'];

    public function __construct()
    {
        $this->model = new IpdMedicationOrder();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId)
    {
        return $this->newQuery()
            ->with(['administrations'])
            ->where('admission_id', $admissionId)
            ->orderByDesc('ordered_at')
            ->get();
    }

    public function withAdministrations(int $id): IpdMedicationOrder
    {
        return $this->newQuery()->with(['administrations'])->findOrFail($id);
    }
}
