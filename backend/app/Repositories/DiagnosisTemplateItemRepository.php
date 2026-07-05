<?php

namespace App\Repositories;

use App\Models\DiagnosisTemplateItem;
use App\Services\ODataService;

class DiagnosisTemplateItemRepository extends BaseRepository
{
    /**
    * @var DiagnosisTemplateItem
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new DiagnosisTemplateItem();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
