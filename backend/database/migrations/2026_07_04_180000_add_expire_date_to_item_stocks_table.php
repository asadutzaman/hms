<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The stock-on-hand ledger (item_stocks) never carried an expiry date —
 * expiry was only captured per-line on goods_receive_note_items.expire_date
 * and never propagated forward, so near-expiry alerts and FEFO issuing had
 * nothing to query. Add it here and propagate it in
 * GoodsReceiveNoteApprovalController::stockItem().
 */
class AddExpireDateToItemStocksTable extends Migration
{
    public function up()
    {
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->date('expire_date')->nullable()->after('balance_quantity');
        });
    }

    public function down()
    {
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->dropColumn('expire_date');
        });
    }
}
