<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widen opd_bill_items.item_type CHECK constraint to allow 'package' rows
 * (Sprint 7 Package & Bundle Billing explodes a fixed-price package into
 * bill item rows of this new type, same convention as ipd_bill_items,
 * which is a plain string column with no DB-level constraint).
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
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_item_type_check CHECK (item_type IN ('consultation','prescription','investigation','package','other'))");
    }

    public function down(): void
    {
        $table = $this->prefixed('opd_bill_items');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_item_type_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_item_type_check CHECK (item_type IN ('consultation','prescription','investigation','other'))");
    }
};
