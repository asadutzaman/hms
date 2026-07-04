<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OpdVisitAuditLog and OpdVisitRepository::logAudit() unconditionally write
 * `remarks` and `occurred_at`, but the original migration never created
 * those columns — every logAudit() call (visit transitions, cancellations,
 * bill generation, payment recording, vitals/diagnosis/prescription saves)
 * was throwing "column does not exist" and rolling back its parent
 * transaction. Add the missing columns to match what the code already sends.
 */
class AddMissingColumnsToOpdVisitAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::table('opd_visit_audit_logs', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('payload');
            $table->timestamp('occurred_at')->nullable()->after('remarks');
        });
    }

    public function down()
    {
        Schema::table('opd_visit_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['remarks', 'occurred_at']);
        });
    }
}
