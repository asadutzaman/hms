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
        Schema::create('diagnosis_template_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('diagnosis_template_id')->constrained('diagnosis_templates')->cascadeOnDelete();
            $table->string('diagnosis_type')->default('primary');
            $table->foreignId('icd10_id')->nullable()->constrained('icd10_codes')->nullOnDelete();
            $table->string('icd10_code')->nullable();
            $table->string('icd10_description')->nullable();
            $table->string('diagnosis_name');
            $table->text('notes')->nullable();
            $table->integer('sequence')->default(1);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index('diagnosis_template_id', 'dti_template_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosis_template_items');
    }
};
