<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widen workflows.type CHECK constraint for Sprint 9's LeaveRequest
 * approval flow — same pattern as add_purchase_order_to_workflows_type_enum
 * (see project_hms_workflow_engine_and_scaffolding_quirks memory).
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
        $table = $this->prefixed('workflows');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_type_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_type_check CHECK (type IN ('Requisition','GoodsReceiveNote','StockAdjustment','StockTransfer','PurchaseOrder','LeaveRequest'))");
    }

    public function down(): void
    {
        $table = $this->prefixed('workflows');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_type_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_type_check CHECK (type IN ('Requisition','GoodsReceiveNote','StockAdjustment','StockTransfer','PurchaseOrder'))");
    }
};
