<?php

namespace App\Repositories;

use App\Models\NotificationTemplate;
use App\Services\ODataService;

class NotificationTemplateRepository extends BaseRepository
{
    /**
    * @var NotificationTemplate
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['key', 'name'];

    public function __construct()
    {
        $this->model = new NotificationTemplate();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function findByKey(string $key): ?NotificationTemplate
    {
        return $this->newQuery()->where('key', $key)->where('is_active', true)->first();
    }
}
