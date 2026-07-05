<?php

namespace App\Repositories;

use App\Models\Icd10Code;
use App\Services\ODataService;

class Icd10CodeRepository extends BaseRepository
{
    /**
    * @var Icd10Code
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new Icd10Code();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
