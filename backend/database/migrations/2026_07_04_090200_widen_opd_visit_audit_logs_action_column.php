<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The `action` column was created as a DB-level enum (Postgres check constraint)
 * listing only 7 values, but App\Enums\OpdVisitActionEnum (and the repositories
 * that call OpdVisitRepository::logAudit()) already use several more
 * (vitals_saved, diagnosis_saved, prescription_saved, order_saved, waived) —
 * every one of those calls was failing with a check-constraint violation.
 * Widen the column to a plain string, matching appointment_audit_logs, so any
 * enum value from the PHP side is accepted.
 */
class WidenOpdVisitAuditLogsActionColumn extends Migration
{
    public function up()
    {
        $table = DB::getTablePrefix() . 'opd_visit_audit_logs';

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_action_check");

        Schema::table('opd_visit_audit_logs', function (Blueprint $table) {
            $table->string('action', 60)->default('update')->change();
        });
    }

    public function down()
    {
        // Column widening is not reversed; re-adding the narrower check
        // constraint could fail against rows written with the newer action
        // values, so this migration is intentionally one-way.
    }
}
