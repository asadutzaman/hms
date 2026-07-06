<?php

namespace App\Repositories;

use App\Models\IpdVital;
use App\Services\ODataService;

class IpdVitalRepository extends BaseRepository
{
    /**
    * @var IpdVital
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['notes'];

    public function __construct()
    {
        $this->model = new IpdVital();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId)
    {
        return $this->newQuery()
            ->where('admission_id', $admissionId)
            ->orderBy('recorded_at')
            ->get();
    }

    public function latestForAdmission(int $admissionId): ?IpdVital
    {
        return $this->newQuery()
            ->where('admission_id', $admissionId)
            ->orderByDesc('recorded_at')
            ->first();
    }
}
