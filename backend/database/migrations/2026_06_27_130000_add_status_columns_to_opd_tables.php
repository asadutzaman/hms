<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * imake:crud generates every model with `protected $attributes['status'] = 1;`
 * (StatusEnum::ACTIVE), so any table missing a `status` column blows up on save.
 * Add the column to all OPD tables that don't have one yet (idempotent).
 */
return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'opd_vitals',
            'opd_diagnoses',
            'opd_prescriptions',
            'opd_prescription_items',
            'opd_investigation_order_items',
            'opd_bill_items',
            'opd_bill_payments',
            'opd_visit_audit_logs',
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t) && ! Schema::hasColumn($t, 'status')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->after('status_flag');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'opd_vitals',
            'opd_diagnoses',
            'opd_prescriptions',
            'opd_prescription_items',
            'opd_investigation_order_items',
            'opd_bill_items',
            'opd_bill_payments',
            'opd_visit_audit_logs',
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'status')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }
};