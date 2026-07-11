<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scope: Doctor Schedule module only. Repoint doctor_id from employees.id to
 * users.id on doctor_schedules, doctor_schedule_exceptions and
 * appointment_slots (the schedule → materialized-slot chain), then let the
 * demo seeder rebuild the rows with user ids. We clear these tables rather
 * than translate in place because existing employee→user links contain
 * duplicates/stale ids (the seeder documents this) that collide with the
 * appointment_slots unique key. Other domains (appointments, OPD, IPD, OT,
 * referrals) keep doctor_id → employees and are intentionally untouched.
 */
return new class extends Migration
{
    private array $tables = ['doctor_schedules', 'doctor_schedule_exceptions', 'appointment_slots'];

    public function up(): void
    {
        $this->repoint('employees', 'users');
    }

    public function down(): void
    {
        $this->repoint('users', 'employees');
    }

    private function repoint(string $from, string $to): void
    {
        $p = DB::getTablePrefix();

        // Drop the doctor_id FKs first so the clear-out can't be blocked.
        foreach ($this->tables as $t) {
            if (Schema::hasColumn($t, 'doctor_id')) {
                DB::statement("ALTER TABLE {$p}{$t} DROP CONSTRAINT IF EXISTS {$t}_doctor_id_foreign");
            }
        }

        // Clear the schedule chain (children before parents). The seeder
        // repopulates it keyed on the new reference table's ids.
        foreach (['appointment_slots', 'doctor_schedule_exceptions', 'doctor_schedule_slots', 'doctor_schedules'] as $t) {
            if (Schema::hasTable($t)) {
                DB::statement("DELETE FROM {$p}{$t}");
            }
        }

        // Re-add the FKs pointing at the target table.
        foreach ($this->tables as $t) {
            if (Schema::hasColumn($t, 'doctor_id')) {
                DB::statement(
                    "ALTER TABLE {$p}{$t} ADD CONSTRAINT {$t}_doctor_id_foreign " .
                    "FOREIGN KEY (doctor_id) REFERENCES {$p}{$to}(id) ON DELETE CASCADE"
                );
            }
        }
    }
};
