<?php

namespace App\Repositories;

use App\Models\RadiologyReportTemplate;
use App\Services\ODataService;

class RadiologyReportTemplateRepository extends BaseRepository
{
    /**
    * @var RadiologyReportTemplate
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['name', 'body_part'];

    public function __construct()
    {
        $this->model = new RadiologyReportTemplate();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
