<?php

namespace App\Repositories;

use App\Models\RadiologyOrder;
use App\Services\ODataService;

class RadiologyOrderRepository extends BaseRepository
{
    /**
    * @var RadiologyOrder
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['rad_order_no', 'clinical_indication'];

    public function __construct()
    {
        $this->model = new RadiologyOrder();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withRelations(int $id): RadiologyOrder
    {
        return $this->newQuery()->with(['patient', 'items.report'])->findOrFail($id);
    }

    public function forPatient(int $patientId)
    {
        return $this->newQuery()->with(['items'])->where('patient_id', $patientId)->orderByDesc('ordered_at')->get();
    }

    public function forOpdVisit(int $opdVisitId)
    {
        return $this->newQuery()->with(['items'])->where('opd_visit_id', $opdVisitId)->orderByDesc('ordered_at')->get();
    }

    public function forIpdAdmission(int $admissionId)
    {
        return $this->newQuery()->with(['items'])->where('ipd_admission_id', $admissionId)->orderByDesc('ordered_at')->get();
    }

    /** The radiology worklist — active (non-terminal) orders, oldest first. */
    public function activeWorklist()
    {
        return $this->newQuery()
            ->with(['patient', 'items'])
            ->whereNotIn('order_status', ['reported', 'cancelled'])
            ->orderBy('ordered_at')
            ->get();
    }
}
