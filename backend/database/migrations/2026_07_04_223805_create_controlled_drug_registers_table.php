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
        Schema::create('controlled_drug_registers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->bigInteger('drug_id')->comment('FK=drugs.id');
            $table->bigInteger('patient_id')->comment('FK=patients.id');
            $table->bigInteger('opd_prescription_item_id')->comment('FK=opd_prescription_items.id');
            $table->float('dispensed_quantity');
            $table->bigInteger('dispensed_by')->comment('FK=users.id');
            $table->bigInteger('witnessed_by')->comment('FK=users.id');
            $table->timestamp('dispensed_at');
            $table->string('remarks')->nullable();

            $table->timestamps();

            $table->index('drug_id', 'cdr_drug_id_idx');
            $table->index('patient_id', 'cdr_patient_id_idx');
            $table->index('dispensed_at', 'cdr_dispensed_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('controlled_drug_registers');
    }
};
