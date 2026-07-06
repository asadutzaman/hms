<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_discharge_summaries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('admission_id')->unique()->constrained('ipd_admissions')->cascadeOnDelete();

            $table->string('summary_no', 30)->unique();

            $table->text('admission_diagnosis')->nullable();
            $table->text('discharge_diagnosis')->nullable();
            $table->text('hospital_course')->nullable();
            $table->text('procedures_performed')->nullable();
            $table->string('discharge_condition', 20)->nullable(); // stable, improved, unchanged, deteriorated
            $table->json('discharge_medications')->nullable(); // snapshot of active orders at discharge time
            $table->text('follow_up_instructions')->nullable();
            $table->text('discharge_advice')->nullable();

            $table->boolean('is_finalized')->default(false);
            $table->unsignedBigInteger('signed_by')->nullable();
            $table->timestamp('signed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_discharge_summaries');
    }
};
