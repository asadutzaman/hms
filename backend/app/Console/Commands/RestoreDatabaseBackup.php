<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

/**
 * Deliberately CLI-only — restoring a backup overwrites the live database,
 * so it is not exposed as a one-click UI button (see BackupController /
 * the admin Backup page, which only lists/downloads/triggers backups).
 * An operator must run this manually with the exact filename and confirm.
 */
class RestoreDatabaseBackup extends Command
{
    protected $signature = 'hms:restore {filename : The backup filename under storage/app/backups}';

    protected $description = 'Restore the database from a backup file created by hms:backup. DESTRUCTIVE — overwrites the current database.';

    public function handle()
    {
        $filename = $this->argument('filename');
        $path = storage_path('app/backups/' . basename($filename));

        if (!file_exists($path)) {
            $this->error("Backup file not found: {$path}");
            return self::FAILURE;
        }

        $connection = config('database.connections.' . config('database.default'));

        $this->warn("This will OVERWRITE the '{$connection['database']}' database with the contents of {$filename}.");
        if (!$this->confirm('Are you sure you want to continue?', false)) {
            $this->info('Restore cancelled.');
            return self::SUCCESS;
        }

        $pgRestorePath = env('PG_RESTORE_PATH', 'pg_restore');

        $result = Process::env(['PGPASSWORD' => $connection['password']])
            ->timeout(1200)
            ->run([
                $pgRestorePath,
                '-h', $connection['host'],
                '-p', (string) $connection['port'],
                '-U', $connection['username'],
                '-d', $connection['database'],
                '--clean',
                '--if-exists',
                $path,
            ]);

        if (!$result->successful()) {
            $this->error($result->errorOutput() ?: 'pg_restore exited with a non-zero status.');
            return self::FAILURE;
        }

        $this->info('Restore completed successfully.');
        return self::SUCCESS;
    }
}
