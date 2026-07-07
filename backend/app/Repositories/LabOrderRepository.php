<?php

namespace App\Repositories;

use App\Models\LabOrder;
use App\Services\ODataService;

class LabOrderRepository extends BaseRepository
{
    /**
    * @var LabOrder
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['lab_order_no', 'clinical_indication'];

    public function __construct()
    {
        $this->model = new LabOrder();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withRelations(int $id): LabOrder
    {
        return $this->newQuery()
            ->with(['patient', 'items.results', 'samples'])
            ->findOrFail($id);
    }

    public function forPatient(int $patientId)
    {
        return $this->newQuery()
            ->with(['items', 'samples'])
            ->where('patient_id', $patientId)
            ->orderByDesc('ordered_at')
            ->get();
    }

    public function forOpdVisit(int $opdVisitId)
    {
        return $this->newQuery()->with(['items', 'samples'])->where('opd_visit_id', $opdVisitId)->orderByDesc('ordered_at')->get();
    }

    public function forIpdAdmission(int $admissionId)
    {
        return $this->newQuery()->with(['items', 'samples'])->where('ipd_admission_id', $admissionId)->orderByDesc('ordered_at')->get();
    }

    /** The lab worklist — active (non-terminal) orders, oldest first. */
    public function activeWorklist()
    {
        return $this->newQuery()
            ->with(['patient', 'items', 'samples'])
            ->whereNotIn('order_status', ['reported', 'cancelled'])
            ->orderBy('ordered_at')
            ->get();
    }
}
