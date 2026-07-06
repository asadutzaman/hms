<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * IpdAdmissionRepository::generateAdmissionNo() and IpdBillService's bill-number
 * generation insert into code_sequences with label IPD_ADMISSION / IPD_BILL —
 * widen the CHECK constraint to allow them (same pattern as
 * add_opd_labels_to_code_sequences_label_enum.php).
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
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL','PURCHASE_ORDER','IPD_ADMISSION','IPD_BILL'))");
    }

    public function down(): void
    {
        $table = $this->prefixed('code_sequences');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_label_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL','PURCHASE_ORDER'))");
    }
};
