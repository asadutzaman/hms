<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * OpdVisitRepository::generateOpdNo() and OpdBillRepository::generateBillNo()
 * insert into code_sequences with label OPD_VISIT / OPD_BILL, but the check
 * constraint never allowed those values — every OPD visit created for a new
 * calendar date, and every bill generated, was failing this constraint.
 */
class AddOpdLabelsToCodeSequencesLabelEnum extends Migration
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
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL'))");
    }

    public function down()
    {
        $table = $this->prefixed('code_sequences');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_label_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT'))");
    }
}
