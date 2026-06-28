<?php

namespace App\Repositories;

use App\Models\OpdVisitAuditLog;
use App\Services\ODataService;

class OpdVisitAuditLogRepository extends BaseRepository
{
    /**
     * @var OpdVisitAuditLog
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'action',
        'from_status',
        'to_status',
        'remarks',
    ];

    public function __construct()
    {
        $this->model = new OpdVisitAuditLog();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forVisit(int $visitId)
    {
        return $this->newQuery()
            ->where('opd_visit_id', $visitId)
            ->orderBy('occurred_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }
}
