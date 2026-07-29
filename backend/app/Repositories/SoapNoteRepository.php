<?php

namespace App\Repositories;

use App\Models\SoapNote;
use App\Services\ODataService;

class SoapNoteRepository extends BaseRepository
{
    /** @var SoapNote */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['subjective', 'objective', 'assessment', 'plan'];

    public function __construct()
    {
        $this->model = new SoapNote();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
