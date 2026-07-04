<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPurchaseOrderToCodeSequencesLabelEnum extends Migration
{
    private function prefixed(string $table): string
    {
        $prefix = DB::getTablePrefix();
        return $prefix . $table;
    }

    public function up()
    {
        $table = $this->prefixed('code_sequences');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_label_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL','PURCHASE_ORDER'))");
    }

    public function down()
    {
        $table = $this->prefixed('code_sequences');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_label_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL'))");
    }
}
