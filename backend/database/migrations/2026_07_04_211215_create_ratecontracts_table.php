<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_contracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->bigInteger('supplier_id')->index()->comment('FK=suppliers.id');
            $table->bigInteger('item_id')->index()->comment('FK=items.id');
            $table->bigInteger('vendor_quote_id')->nullable()->comment('FK=vendor_quotes.id, source quote this contract was created from');
            $table->decimal('contract_price', 12, 2);
            $table->date('valid_from');
            $table->date('valid_to');
            $table->enum('contract_status', ['pending_approval', 'active', 'expired', 'cancelled'])->default('pending_approval')->index();
            $table->string('process_status')->default('DRAFT')->comment('DRAFT, SUBMITTED, APPROVED, REJECTED');
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['item_id', 'supplier_id', 'contract_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rate_contracts');
    }
}
