<?php

namespace App\Repositories;

use App\Models\ShiftHandover;
use App\Services\ODataService;

class ShiftHandoverRepository extends BaseRepository
{
    /** @var ShiftHandover */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['summary', 'shift_label'];

    public function __construct()
    {
        $this->model = new ShiftHandover();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
