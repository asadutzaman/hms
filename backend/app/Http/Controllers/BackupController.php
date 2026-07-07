<?php

namespace App\Http\Controllers;

use App\Http\Resources\BackupLogResource;
use App\Services\Backup\BackupService;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;

class BackupController extends Controller
{
    use RestControllerTrait;

    private $resource;

    public function __construct()
    {
        $this->resource = BackupLogResource::class;
    }

    /** GET /backup — recent backup history. */
    public function index()
    {
        try {
            $rows = app(BackupService::class)->listBackups();
            $response = $this->resource::collection($rows)->toArray(request());
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /backup/run — manual "Run Backup Now". */
    public function run()
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BackupService::class)->runBackup('manual', $actorId);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /backup/{id}/download */
    public function download($id)
    {
        try {
            $path = app(BackupService::class)->downloadPath((int) $id);
            return response()->download($path);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
