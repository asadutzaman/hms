<?php

namespace App\Repositories;

use App\Models\PrescriptionTemplateItem;
use App\Services\ODataService;

class PrescriptionTemplateItemRepository extends BaseRepository
{
    /**
    * @var PrescriptionTemplateItem
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new PrescriptionTemplateItem();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
