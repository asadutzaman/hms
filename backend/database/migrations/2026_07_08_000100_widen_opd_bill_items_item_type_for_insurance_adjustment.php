<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widen opd_bill_items.item_type CHECK constraint to allow
 * 'insurance_adjustment' rows (F-20-05 Settlement & Reconciliation bills
 * the patient for the shortfall between claimed/approved and the amount
 * actually settled by the insurer). Same pattern as
 * widen_opd_bill_items_item_type_check.php (Sprint 7's 'package' addition).
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
        $table = $this->prefixed('opd_bill_items');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_item_type_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_item_type_check CHECK (item_type IN ('consultation','prescription','investigation','package','insurance_adjustment','other'))");
    }

    public function down(): void
    {
        $table = $this->prefixed('opd_bill_items');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_item_type_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_item_type_check CHECK (item_type IN ('consultation','prescription','investigation','package','other'))");
    }
};
