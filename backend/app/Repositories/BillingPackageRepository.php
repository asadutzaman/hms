<?php

namespace App\Repositories;

use App\Models\BillingPackage;
use App\Services\ODataService;

class BillingPackageRepository extends BaseRepository
{
    /**
    * @var BillingPackage
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['code', 'name'];

    public function __construct()
    {
        $this->model = new BillingPackage();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withItems(int $id): BillingPackage
    {
        return $this->newQuery()->with(['items'])->findOrFail($id);
    }
}
