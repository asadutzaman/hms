<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Services\ODataService;

class NotificationRepository extends BaseRepository
{
    /**
    * @var Notification
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'body'];

    public function __construct()
    {
        $this->model = new Notification();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forUser(int $userId, int $limit = 30)
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('channel', 'in_app')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function unreadCountForUser(int $userId): int
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('channel', 'in_app')
            ->where('is_read', false)
            ->count();
    }
}
