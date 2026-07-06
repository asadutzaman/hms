<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Only one 'admitted' admission per bed at any time. Postgres partial unique
 * index (DB is confirmed Postgres here). App-level defense-in-depth guard is
 * IpdAdmissionRepository::assertBedIsFree(), which locks the target bed row
 * before checking — see repository docblock for why that ordering matters
 * for bed transfers, not just fresh admissions.
 */
return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('database.connections.' . config('database.default') . '.prefix', '');
        $table  = $prefix . 'ipd_admissions';

        $driver = DB::connection()->getDriverName();
        if ($driver !== 'pgsql') {
            return;
        }

        DB::statement(<<<SQL
            CREATE UNIQUE INDEX IF NOT EXISTS idx_ipd_admissions_one_active_per_bed
            ON {$table} (bed_id)
            WHERE admission_status = 'admitted' AND deleted_at IS NULL
        SQL);
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS idx_ipd_admissions_one_active_per_bed');
    }
};
