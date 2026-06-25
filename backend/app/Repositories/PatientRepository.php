<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Services\ODataService;

class PatientRepository extends BaseRepository
{
    /**
     * @var Patient
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['first_name', 'mrn'];

    public function __construct()
    {
        $this->model         = new Patient();
    }
    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
