<?php

namespace App\Repositories;

use App\Models\PreAuthorization;
use App\Services\ODataService;

class PreAuthorizationRepository extends BaseRepository
{
    /**
    * @var PreAuthorization
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['pa_no', 'policy_number'];

    public function __construct()
    {
        $this->model = new PreAuthorization();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withRelations(int $id): PreAuthorization
    {
        return $this->newQuery()->with(['patient', 'insuranceCompany', 'insuranceScheme'])->findOrFail($id);
    }

    public function forPatient(int $patientId)
    {
        return $this->newQuery()
            ->with(['insuranceCompany', 'insuranceScheme'])
            ->where('patient_id', $patientId)
            ->orderByDesc('requested_at')
            ->get();
    }

    public function pending()
    {
        return $this->newQuery()
            ->with(['patient', 'insuranceCompany'])
            ->whereIn('pa_status', ['submitted', 'under_review'])
            ->orderBy('requested_at')
            ->get();
    }
}
