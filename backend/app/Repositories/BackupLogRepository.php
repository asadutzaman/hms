<?php

namespace App\Repositories;

use App\Models\BackupLog;
use App\Services\ODataService;

class BackupLogRepository extends BaseRepository
{
    /**
    * @var BackupLog
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['filename'];

    public function __construct()
    {
        $this->model = new BackupLog();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function recent(int $limit = 30)
    {
        return $this->newQuery()->orderByDesc('started_at')->limit($limit)->get();
    }
}
