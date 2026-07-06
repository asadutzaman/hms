<?php

namespace App\Repositories;

use App\Models\IpdNursingAssessment;
use App\Services\ODataService;

class IpdNursingAssessmentRepository extends BaseRepository
{
    /**
    * @var IpdNursingAssessment
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['general_appearance'];

    public function __construct()
    {
        $this->model = new IpdNursingAssessment();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId): ?IpdNursingAssessment
    {
        return $this->newQuery()->where('admission_id', $admissionId)->first();
    }
}
