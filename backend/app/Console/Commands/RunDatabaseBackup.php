<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

class RunDatabaseBackup extends Command
{
    protected $signature = 'hms:backup';

    protected $description = 'Dump the database to storage/app/backups (used by both the daily schedule and the manual "Run Backup Now" admin action)';

    public function handle(BackupService $backupService)
    {
        $log = $backupService->runBackup('scheduled');

        if ($log->backup_status === 'success') {
            $this->info("Backup succeeded: {$log->filename} ({$log->size_bytes} bytes)");
            return self::SUCCESS;
        }

        $this->error("Backup failed: {$log->failure_reason}");
        return self::FAILURE;
    }
}
