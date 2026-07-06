<?php

namespace App\Repositories;

use App\Models\IpdDischargeSummary;
use App\Services\ODataService;

class IpdDischargeSummaryRepository extends BaseRepository
{
    /**
    * @var IpdDischargeSummary
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['summary_no', 'discharge_diagnosis'];

    public function __construct()
    {
        $this->model = new IpdDischargeSummary();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId): ?IpdDischargeSummary
    {
        return $this->newQuery()->where('admission_id', $admissionId)->first();
    }
}
