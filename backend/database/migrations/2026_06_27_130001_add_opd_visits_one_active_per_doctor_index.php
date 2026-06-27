<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Design §1.4: only one `in_consultation` visit per doctor at any time.
 * Postgres-friendly partial unique index. (MySQL has no partial-index support;
 * the application-level `SELECT ... FOR UPDATE` guard in
 * OpdVisitRepository::transitionStatus covers MySQL deployments.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('database.connections.' . config('database.default') . '.prefix', '');
        $table  = $prefix . 'opd_visits';

        // Detect driver from the current default connection.
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'pgsql') {
            // MySQL / MariaDB — skip; rely on the repo-level lock.
            return;
        }

        DB::statement(<<<SQL
            CREATE UNIQUE INDEX IF NOT EXISTS idx_opd_visits_one_active_per_doctor
            ON {$table} (doctor_id)
            WHERE status = 'in_consultation' AND deleted_at IS NULL
        SQL);
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS idx_opd_visits_one_active_per_doctor');
    }
};