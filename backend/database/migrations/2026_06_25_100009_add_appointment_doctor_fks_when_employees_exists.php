<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add FK constraints on doctor_id columns once the employees table exists.
 *
 * The 2026_06_25_100001..100006 migrations were created before the
 * employees table (Sprint 1 Employee Master backend is still pending).
 * They use unsignedBigInteger for doctor_id without an FK. This migration
 * re-asserts the FK by introspecting the schema and adding the constraint
 * only when the employees table is present. Idempotent: safe to re-run.
 */
class AddAppointmentDoctorFksWhenEmployeesExists extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('employees')) {
            // Employees table is not created yet. Skip — re-run later.
            return;
        }

        $tables = [
            'doctor_schedules'             => 'CASCADE',
            'doctor_schedule_exceptions'   => 'CASCADE',
            'appointment_slots'            => 'CASCADE',
            'appointments'                 => 'CASCADE',
            'appointment_waitlists'        => 'CASCADE',
            'appointment_audit_logs'       => 'SET NULL',
        ];

        foreach ($tables as $table => $onDelete) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'doctor_id')) {
                continue;
            }

            $prefixedTable = DB::getTablePrefix() . $table;
            $constraintName = $table . '_doctor_id_foreign';

            $exists = DB::selectOne(
                "SELECT 1 FROM pg_constraint WHERE conname = ?",
                [$constraintName]
            );

            if ($exists) {
                continue;
            }

            DB::statement(
                "ALTER TABLE {$prefixedTable} ADD CONSTRAINT {$constraintName} " .
                "FOREIGN KEY (doctor_id) REFERENCES " . DB::getTablePrefix() . "employees(id) ON DELETE {$onDelete}"
            );
        }
    }

    public function down()
    {
        $tables = [
            'doctor_schedules',
            'doctor_schedule_exceptions',
            'appointment_slots',
            'appointments',
            'appointment_waitlists',
            'appointment_audit_logs',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $prefixedTable = DB::getTablePrefix() . $table;
            $constraintName = $table . '_doctor_id_foreign';
            DB::statement("ALTER TABLE {$prefixedTable} DROP CONSTRAINT IF EXISTS {$constraintName}");
        }
    }
}