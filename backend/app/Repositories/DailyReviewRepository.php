<?php

namespace App\Repositories;

use App\Models\DailyReview;
use App\Services\ODataService;

class DailyReviewRepository extends BaseRepository
{
    /** @var DailyReview */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['progress_note', 'assessment', 'plan'];

    public function __construct()
    {
        $this->model = new DailyReview();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
