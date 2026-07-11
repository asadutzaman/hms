<?php

namespace App\Repositories;

use App\Models\PrescriptionTemplate;
use App\Services\ODataService;

class PrescriptionTemplateRepository extends BaseRepository
{
    /**
    * @var PrescriptionTemplate
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new PrescriptionTemplate();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    // Detail view always needs the drug lines for edit-prefill.
    public function show($id)
    {
        return $this->model->with('items')->findOrFail($id);
    }
}
