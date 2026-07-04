<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('po_number')->unique();
            $table->bigInteger('supplier_id')->index()->comment('FK=suppliers.id');
            $table->bigInteger('branch_id')->index()->comment('FK=branches.id');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('po_status', ['draft', 'pending_approval', 'approved', 'sent', 'partially_received', 'completed', 'cancelled'])->default('draft')->index();
            $table->string('process_status')->default('DRAFT')->comment('DRAFT, SUBMITTED, APPROVED, REJECTED, BACKWARD_INITIATION');
            $table->text('notes')->nullable();
            $table->bigInteger('requisition_id')->nullable()->comment('FK=requisitions.id, set when created from a requisition shortfall');
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
