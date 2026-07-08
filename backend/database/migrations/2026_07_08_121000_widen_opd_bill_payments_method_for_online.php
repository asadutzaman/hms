<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * F-17-05 / F-02-09 Online Payment — widen opd_bill_payments.payment_method
 * CHECK constraint to allow 'online' (gateway) payments. ipd_bill_payments
 * has no such constraint (plain string column) — no migration needed there.
 */
return new class extends Migration
{
    private function prefixed(string $table): string
    {
        $prefix = DB::getTablePrefix();
        return $prefix . $table;
    }

    public function up(): void
    {
        $table = $this->prefixed('opd_bill_payments');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_payment_method_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_payment_method_check CHECK (payment_method IN ('cash','card','insurance','mobile','bank','online','other'))");
    }

    public function down(): void
    {
        $table = $this->prefixed('opd_bill_payments');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_payment_method_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_payment_method_check CHECK (payment_method IN ('cash','card','insurance','mobile','bank','other'))");
    }
};
