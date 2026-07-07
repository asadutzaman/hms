<?php

namespace App\Repositories;

use App\Models\InsuranceClaim;
use App\Services\ODataService;

class InsuranceClaimRepository extends BaseRepository
{
    /**
    * @var InsuranceClaim
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['claim_no', 'policy_number'];

    public function __construct()
    {
        $this->model = new InsuranceClaim();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withRelations(int $id): InsuranceClaim
    {
        return $this->newQuery()->with(['patient', 'insuranceCompany', 'insuranceScheme', 'preAuthorization'])->findOrFail($id);
    }

    public function forPatient(int $patientId)
    {
        return $this->newQuery()
            ->with(['insuranceCompany'])
            ->where('patient_id', $patientId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function forBillable(string $billableType, int $billableId)
    {
        return $this->newQuery()
            ->with(['insuranceCompany'])
            ->where('billable_type', $billableType)
            ->where('billable_id', $billableId)
            ->orderByDesc('created_at')
            ->get();
    }
}
