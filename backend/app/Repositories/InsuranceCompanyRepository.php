<?php

namespace App\Repositories;

use App\Models\InsuranceCompany;
use App\Services\ODataService;

class InsuranceCompanyRepository extends BaseRepository
{
    /**
    * @var InsuranceCompany
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['code', 'name', 'contact_person'];

    public function __construct()
    {
        $this->model = new InsuranceCompany();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withSchemes(int $id): InsuranceCompany
    {
        return $this->newQuery()->with(['schemes'])->findOrFail($id);
    }
}
