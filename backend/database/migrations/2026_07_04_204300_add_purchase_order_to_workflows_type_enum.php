<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPurchaseOrderToWorkflowsTypeEnum extends Migration
{
    private function prefixed(string $table): string
    {
        $prefix = DB::getTablePrefix();
        return $prefix . $table;
    }

    public function up()
    {
        $table = $this->prefixed('workflows');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_type_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_type_check CHECK (type IN ('Requisition','GoodsReceiveNote','StockAdjustment','StockTransfer','PurchaseOrder'))");
    }

    public function down()
    {
        $table = $this->prefixed('workflows');

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_type_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_type_check CHECK (type IN ('Requisition','GoodsReceiveNote','StockAdjustment','StockTransfer'))");
    }
}
