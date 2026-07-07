<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('lab_order_item_id')->constrained('lab_order_items')->cascadeOnDelete();
            $table->unsignedBigInteger('lab_test_parameter_id')->nullable();

            $table->string('parameter_name_snapshot');
            $table->string('unit_snapshot', 30)->nullable();
            $table->string('result_value')->nullable();
            $table->string('result_flag', 20)->nullable(); // normal, low, high, critical_low, critical_high
            $table->string('reference_range_display', 100)->nullable(); // snapshot of the range shown at entry time

            $table->string('verification_status', 20)->default('pending')->index(); // pending, verified, amended
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->timestamp('entered_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('lab_test_parameter_id', 'lab_results_lab_test_parameter_id_fk')->references('id')->on('lab_test_parameters')->onDelete('set null');
            $table->index(['lab_order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
