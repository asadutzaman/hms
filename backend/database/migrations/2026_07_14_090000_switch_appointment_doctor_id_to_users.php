<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bring the appointment domain in line with the Doctor Schedule module:
 * doctor_id references users.id, not employees.id.
 *
 * Without this, the booking form (which now picks doctors from users, matching
 * the schedule/slots) writes a user id into a column FK'd to employees — and
 * because employee ids and user ids overlap numerically, validation passed and
 * the appointment was silently booked against a DIFFERENT doctor.
 *
 * employees.user_id is 1:1 here (no duplicates), so existing rows are
 * translated in place rather than dropped.
 */
return new class extends Migration
{
    private array $tables = ['appointments', 'appointment_waitlists', 'appointment_audit_logs'];

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
        $p    = DB::getTablePrefix();
        $fromT = "{$p}{$from}";
        $toT   = "{$p}{$to}";
        // employees.user_id links the two; reversing means matching the other way.
        $join = $from === 'employees'
            ? "SELECT e.user_id FROM {$fromT} e WHERE e.id = t.doctor_id"
            : "SELECT e.id FROM {$p}employees e WHERE e.user_id = t.doctor_id";

        foreach ($this->tables as $t) {
            if (!Schema::hasTable($t) || !Schema::hasColumn($t, 'doctor_id')) {
                continue;
            }
            $ft = "{$p}{$t}";

            DB::statement("ALTER TABLE {$ft} DROP CONSTRAINT IF EXISTS {$t}_doctor_id_foreign");
            DB::statement("UPDATE {$ft} t SET doctor_id = ({$join}) WHERE ({$join}) IS NOT NULL");
            DB::statement("DELETE FROM {$ft} WHERE doctor_id IS NOT NULL AND doctor_id NOT IN (SELECT id FROM {$toT})");

            $onDelete = $t === 'appointment_audit_logs' ? 'SET NULL' : 'CASCADE';
            DB::statement(
                "ALTER TABLE {$ft} ADD CONSTRAINT {$t}_doctor_id_foreign " .
                "FOREIGN KEY (doctor_id) REFERENCES {$toT}(id) ON DELETE {$onDelete}"
            );
        }
    }
};
