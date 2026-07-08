<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL','PURCHASE_ORDER','IPD_ADMISSION','IPD_BILL','IPD_DISCHARGE_SUMMARY','DEATH_CERTIFICATE','ER_VISIT','LAB_ORDER','LAB_SAMPLE','PRE_AUTH','INSURANCE_CLAIM','BILL_REFUND','RAD_ORDER','OT_BOOKING','LEAVE_REQUEST','CLAIM_SETTLEMENT','BLOOD_DONOR','BLOOD_DONATION','BLOOD_UNIT'))");
    }

    public function down(): void
    {
        $table = $this->prefixed('code_sequences');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_label_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_label_check CHECK (label IN ('SUPPLIER','ITEM','REQUISITION','GRN','STOCK_TRANSFER','STOCK_ADJUSTMENT','PATIENT','APPOINTMENT','OPD_VISIT','OPD_BILL','PURCHASE_ORDER','IPD_ADMISSION','IPD_BILL','IPD_DISCHARGE_SUMMARY','DEATH_CERTIFICATE','ER_VISIT','LAB_ORDER','LAB_SAMPLE','PRE_AUTH','INSURANCE_CLAIM','BILL_REFUND','RAD_ORDER','OT_BOOKING','LEAVE_REQUEST','CLAIM_SETTLEMENT'))");
    }
};
