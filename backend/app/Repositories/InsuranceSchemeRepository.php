<?php

namespace App\Repositories;

use App\Models\InsuranceScheme;
use App\Services\ODataService;

class InsuranceSchemeRepository extends BaseRepository
{
    /**
    * @var InsuranceScheme
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name'];

    public function __construct()
    {
        $this->model = new InsuranceScheme();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function byCompany(int $insuranceCompanyId)
    {
        return $this->newQuery()->where('insurance_company_id', $insuranceCompanyId)->where('is_active', true)->get();
    }
}
