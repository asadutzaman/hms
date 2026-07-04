<?php

namespace App\Repositories;

use App\Models\PatientAllergy;
use App\Services\ODataService;

class PatientAllergyRepository extends BaseRepository
{
    /**
    * @var PatientAllergy
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['allergen_name', 'reaction_type'];

    public function __construct()
    {
        $this->model = new PatientAllergy();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forPatient(int $patientId)
    {
        return $this->newQuery()
            ->with(['drug:id,name_en,name_bn,code'])
            ->where('patient_id', $patientId)
            ->orderByRaw("CASE severity WHEN 'severe' THEN 1 WHEN 'moderate' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
