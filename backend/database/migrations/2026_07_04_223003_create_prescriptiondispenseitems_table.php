<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('opd_prescription_dispense_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->bigInteger('opd_prescription_dispense_id')->comment('FK=opd_prescription_dispenses.id');
            $table->bigInteger('opd_prescription_item_id')->comment('FK=opd_prescription_items.id');
            $table->bigInteger('drug_id')->comment('FK=drugs.id');
            $table->float('dispensed_quantity');
            $table->date('expire_date')->nullable()->comment('batch expiry consumed for this line');
            $table->string('remarks')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index('opd_prescription_dispense_id', 'pdi_dispense_id_idx');
            $table->index('opd_prescription_item_id', 'pdi_prescription_item_id_idx');
            $table->index('drug_id', 'pdi_drug_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opd_prescription_dispense_items');
    }
};
