<?php

namespace App\Repositories;

use App\Models\IpdDeathCertificate;
use App\Services\ODataService;

class IpdDeathCertificateRepository extends BaseRepository
{
    /**
    * @var IpdDeathCertificate
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['certificate_no'];

    public function __construct()
    {
        $this->model = new IpdDeathCertificate();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forAdmission(int $admissionId): ?IpdDeathCertificate
    {
        return $this->newQuery()->where('admission_id', $admissionId)->first();
    }
}
