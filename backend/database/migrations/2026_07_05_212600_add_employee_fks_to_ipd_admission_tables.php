<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent: attaches foreign keys from IPD admission tables to `employees`,
 * mirroring add_opd_employee_fks_when_employees_exists.php.
 *
 * - ipd_admissions.attending_doctor_id  -> employees.id (RESTRICT — never lose doctor history)
 * - ipd_admissions.admitted_by          -> employees.id (NULL on delete)
 * - ipd_admissions.discharged_by        -> employees.id (NULL on delete)
 * - ipd_admission_audit_logs.actor_id   -> employees.id (NULL on delete)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        $fks = [
            // [table, column, fk_name, on_delete]
            ['ipd_admissions',           'attending_doctor_id', 'fk_ipd_adm_attending_doctor', 'restrict'],
            ['ipd_admissions',           'admitted_by',         'fk_ipd_adm_admitted_by',       'set null'],
            ['ipd_admissions',           'discharged_by',       'fk_ipd_adm_discharged_by',     'set null'],
            ['ipd_admission_audit_logs', 'actor_id',            'fk_ipd_adm_audit_actor',       'set null'],
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

    public function down(): void
    {
        $names = [
            'fk_ipd_adm_attending_doctor',
            'fk_ipd_adm_admitted_by',
            'fk_ipd_adm_discharged_by',
            'fk_ipd_adm_audit_actor',
        ];

        $tables = ['ipd_admissions', 'ipd_admission_audit_logs'];

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
};
