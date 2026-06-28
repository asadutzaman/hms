<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent: attaches foreign keys from OPD tables to `employees` once that
 * table exists. Mirrors the pattern used in Sprint 2 for appointments
 * (see `add_appointment_doctor_fks_when_employees_exists.php`).
 *
 * - opd_visits.doctor_id           -> employees.id
 * - opd_visits.created_by          -> employees.id (NULL on delete)
 * - opd_visits.updated_by          -> employees.id (NULL on delete)
 * - opd_visits.closed_by           -> employees.id (NULL on delete)
 * - opd_visit_audit_logs.actor_id  -> employees.id (NULL on delete)
 * - opd_prescriptions.prescribed_by -> employees.id (NULL on delete)
 * - opd_investigation_orders.ordered_by -> employees.id (NULL on delete)
 * - opd_bills.billed_by            -> employees.id (NULL on delete)
 * - opd_bill_payments.paid_by      -> employees.id (NULL on delete)
 */
class AddOpdEmployeeFksWhenEmployeesExists extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        // We try each FK independently so a failure on one does not block the rest.
        // - Nullable actor columns (created_by / updated_by / closed_by / actor_id /
        //   prescribed_by / ordered_by / billed_by / paid_by): ON DELETE SET NULL.
        // - NOT NULL actor columns (opd_visits.doctor_id): ON DELETE RESTRICT to
        //   prevent an employee from being removed while they have a visit history.
        $fks = [
            // [table, column, fk_name, on_delete]
            ['opd_visits',               'doctor_id',     'fk_opd_visits_doctor',     'restrict'],
            ['opd_visits',               'created_by',    'fk_opd_visits_created_by', 'set null'],
            ['opd_visits',               'updated_by',    'fk_opd_visits_updated_by', 'set null'],
            ['opd_visits',               'closed_by',     'fk_opd_visits_closed_by',  'set null'],
            ['opd_visit_audit_logs',     'actor_id',      'fk_opd_audit_actor',       'set null'],
            ['opd_prescriptions',        'prescribed_by', 'fk_opd_rx_prescribed_by',  'set null'],
            ['opd_investigation_orders', 'ordered_by',    'fk_opd_inv_ordered_by',    'set null'],
            ['opd_bills',                'billed_by',     'fk_opd_bill_billed_by',    'set null'],
            ['opd_bill_payments',        'paid_by',       'fk_opd_bill_paid_by',      'set null'],
        ];

        $prefix = DB::getTablePrefix();
        $employeesTable = $prefix . 'employees';

        foreach ($fks as [$table, $column, $name, $onDelete]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }
            try {
                $onDeleteSql = $onDelete === 'restrict' ? 'RESTRICT' : 'SET NULL';
                $prefixedTable = $prefix . $table;
                DB::statement(
                    "ALTER TABLE {$prefixedTable} ADD CONSTRAINT {$name} " .
                    "FOREIGN KEY ({$column}) REFERENCES {$employeesTable}(id) ON DELETE {$onDeleteSql}"
                );
            } catch (\Throwable $e) {
                // FK already exists or constraint conflict — ignore (idempotent).
            }
        }
    }

    public function down()
    {
        $names = [
            'fk_opd_visits_doctor',
            'fk_opd_visits_created_by',
            'fk_opd_visits_updated_by',
            'fk_opd_visits_closed_by',
            'fk_opd_audit_actor',
            'fk_opd_rx_prescribed_by',
            'fk_opd_inv_ordered_by',
            'fk_opd_bill_billed_by',
            'fk_opd_bill_paid_by',
        ];

        $tables = [
            'opd_visits',
            'opd_visit_audit_logs',
            'opd_prescriptions',
            'opd_investigation_orders',
            'opd_bills',
            'opd_bill_payments',
        ];

        $prefix = DB::getTablePrefix();

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $prefixedTable = $prefix . $table;
            foreach ($names as $name) {
                try {
                    DB::statement("ALTER TABLE {$prefixedTable} DROP CONSTRAINT IF EXISTS {$name}");
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }
    }
}