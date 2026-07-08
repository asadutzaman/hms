<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-16-03 Chronic Disease Management. condition_status (not "status" — the
 * project's generic active/inactive flag already owns that column name on
 * every table, see project_hms_workflow_engine_and_scaffolding_quirks
 * memory) tracks active/monitoring/resolved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_chronic_conditions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('patient_id')->index();
            $table->string('condition_name');
            $table->unsignedBigInteger('icd10_code_id')->nullable()->index();
            $table->date('diagnosed_date')->nullable();
            $table->text('target_notes')->nullable();
            $table->string('condition_status', 16)->default('active')->index();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id', 'chronic_conditions_patient_fk')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('icd10_code_id', 'chronic_conditions_icd10_fk')->references('id')->on('icd10_codes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_chronic_conditions');
    }
};
