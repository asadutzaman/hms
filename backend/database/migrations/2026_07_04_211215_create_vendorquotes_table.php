<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_quotes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->bigInteger('purchase_order_id')->nullable()->index()->comment('FK=purchase_orders.id');
            $table->bigInteger('requisition_id')->nullable()->index()->comment('FK=requisitions.id');
            $table->bigInteger('supplier_id')->index()->comment('FK=suppliers.id');
            $table->bigInteger('item_id')->index()->comment('FK=items.id');
            $table->decimal('quoted_unit_price', 12, 2);
            $table->integer('quoted_delivery_days')->nullable();
            $table->boolean('is_selected')->default(false)->index();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['item_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_quotes');
    }
}
