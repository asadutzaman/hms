<?php

namespace App\Repositories;

use App\Models\DiagnosisTemplate;
use App\Services\ODataService;

class DiagnosisTemplateRepository extends BaseRepository
{
    /**
    * @var DiagnosisTemplate
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new DiagnosisTemplate();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
