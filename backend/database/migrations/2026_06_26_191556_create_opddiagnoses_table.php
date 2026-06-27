<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpddiagnosesTable extends Migration
{
    public function up()
    {
        Schema::create('opd_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_visit_id')->constrained('opd_visits')->cascadeOnDelete();

            $table->string('icd10_code', 10);
            $table->string('description', 255);
            $table->enum('diagnosis_type', ['primary', 'secondary'])->default('primary');
            $table->unsignedSmallInteger('sequence')->default(1);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index('icd10_code', 'idx_opd_dx_icd');
            $table->index(['opd_visit_id', 'sequence']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_diagnoses');
    }
}
