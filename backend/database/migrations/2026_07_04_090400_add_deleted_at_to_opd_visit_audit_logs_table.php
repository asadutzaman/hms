<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OpdVisitAuditLog uses the SoftDeletes trait, which adds a global
 * `whereNull('deleted_at')` scope to every query — but the table never had a
 * `deleted_at` column, so every read (including OpdVisitController::auditLog())
 * was throwing "column does not exist".
 */
class AddDeletedAtToOpdVisitAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::table('opd_visit_audit_logs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('opd_visit_audit_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
