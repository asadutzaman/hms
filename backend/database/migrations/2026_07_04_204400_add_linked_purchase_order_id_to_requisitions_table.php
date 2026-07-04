<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkedPurchaseOrderIdToRequisitionsTable extends Migration
{
    public function up()
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->bigInteger('linked_purchase_order_id')->nullable()->index()->after('process_status')->comment('FK=purchase_orders.id, set when a PO was raised for this requisition\'s shortfall');
        });
    }

    public function down()
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropColumn('linked_purchase_order_id');
        });
    }
}
