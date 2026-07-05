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
        Schema::create('opd_prescription_dispenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->bigInteger('opd_prescription_id')->index()->comment('FK=opd_prescriptions.id');
            $table->bigInteger('patient_id')->index()->comment('FK=patients.id');
            $table->bigInteger('branch_id')->index()->comment('FK=branches.id, dispensing counter branch');
            $table->bigInteger('dispensed_by')->nullable()->comment('FK=users.id');
            $table->timestamp('dispensed_at')->nullable();
            $table->string('dispense_status')->default('partial')->comment('partial, completed');
            $table->text('notes')->nullable();

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
     */
    public function down(): void
    {
        Schema::dropIfExists('opd_prescription_dispenses');
    }
};
