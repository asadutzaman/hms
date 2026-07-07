<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widen the code_sequences.label CHECK constraint for Sprint 6's new
 * numbered documents (lab order, sample barcode) — same pattern as
 * add_sprint5_labels_to_code_sequences_label_enum.php.
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
        $table = $this->prefixed('code_sequences');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_label_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL','PURCHASE_ORDER','IPD_ADMISSION','IPD_BILL','IPD_DISCHARGE_SUMMARY','DEATH_CERTIFICATE','ER_VISIT','LAB_ORDER','LAB_SAMPLE'))");
    }

    public function down(): void
    {
        $table = $this->prefixed('code_sequences');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_label_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL','PURCHASE_ORDER','IPD_ADMISSION','IPD_BILL','IPD_DISCHARGE_SUMMARY','DEATH_CERTIFICATE','ER_VISIT'))");
    }
};
