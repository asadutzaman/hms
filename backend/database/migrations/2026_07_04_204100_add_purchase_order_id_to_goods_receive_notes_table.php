<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseOrderIdToGoodsReceiveNotesTable extends Migration
{
    public function up()
    {
        Schema::table('goods_receive_notes', function (Blueprint $table) {
            $table->bigInteger('purchase_order_id')->nullable()->index()->after('supplier_id')->comment('FK=purchase_orders.id, set when this GRN is received against a PO');
        });
    }

    public function down()
    {
        Schema::table('goods_receive_notes', function (Blueprint $table) {
            $table->dropColumn('purchase_order_id');
        });
    }
}
