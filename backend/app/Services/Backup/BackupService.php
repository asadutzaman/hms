<?php

namespace App\Services\Backup;

use App\Exceptions\ApiException;
use App\Models\BackupLog;
use App\Repositories\BackupLogRepository;
use Illuminate\Support\Facades\Process;

class BackupService
{
    protected const RETENTION_COUNT = 14;

    protected BackupLogRepository $repository;

    public function __construct(BackupLogRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Dump the current database via pg_dump (custom format, -F c, so
     * pg_restore can selectively restore later). No spatie/laravel-backup
     * or similar package exists in this app — this shells out directly.
     * The pg_dump binary path is configurable (PG_DUMP_PATH env) since it's
     * not guaranteed to be on PATH in every environment (it isn't on this
     * dev machine — Postgres's client tools are installed but unregistered).
     */
    public function runBackup(string $triggeredByType = 'manual', ?int $triggeredBy = null): BackupLog
    {
        $connection = config('database.connections.' . config('database.default'));
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0770, true);
        }

        $filename = 'backup_' . now()->format('Ymd_His') . '.dump';
        $fullPath = $dir . DIRECTORY_SEPARATOR . $filename;

        $log = BackupLog::query()->create([
            'filename'          => $filename,
            'disk_path'         => $fullPath,
            'backup_status'     => 'running',
            'triggered_by_type' => $triggeredByType,
            'triggered_by'      => $triggeredBy,
            'started_at'        => now(),
        ]);

        $pgDumpPath = env('PG_DUMP_PATH', 'pg_dump');

        try {
            $result = Process::env(['PGPASSWORD' => $connection['password']])
                ->timeout(600)
                ->run([
                    $pgDumpPath,
                    '-h', $connection['host'],
                    '-p', (string) $connection['port'],
                    '-U', $connection['username'],
                    '-F', 'c',
                    '-f', $fullPath,
                    $connection['database'],
                ]);

            if (!$result->successful()) {
                throw new \RuntimeException($result->errorOutput() ?: $result->output() ?: 'pg_dump exited with a non-zero status.');
            }

            $log->backup_status = 'success';
            $log->size_bytes = file_exists($fullPath) ? filesize($fullPath) : null;
            $log->completed_at = now();
            $log->save();

            $this->pruneOldBackups();
        } catch (\Throwable $e) {
            $log->backup_status = 'failed';
            $log->failure_reason = $e->getMessage();
            $log->completed_at = now();
            $log->save();
        }

        return $log->fresh();
    }

    public function listBackups(int $limit = 30)
    {
        return $this->repository->recent($limit);
    }

    public function downloadPath(int $backupLogId): string
    {
        $log = $this->repository->show($backupLogId);
        if ($log->backup_status !== 'success' || !file_exists($log->disk_path)) {
            throw new ApiException('This backup file is not available.', 404);
        }
        return $log->disk_path;
    }

    /** Keep only the most recent RETENTION_COUNT successful backups (files + log rows). */
    protected function pruneOldBackups(): void
    {
        $successful = BackupLog::query()
            ->where('backup_status', 'success')
            ->orderByDesc('started_at')
            ->skip(self::RETENTION_COUNT)
            ->take(1000)
            ->get();

        foreach ($successful as $old) {
            if (file_exists($old->disk_path)) {
                @unlink($old->disk_path);
            }
            $old->delete();
        }
    }
}
