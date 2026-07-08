<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-05-10 Lab Quality Control Module — a QC lot is a control-material
 * batch (e.g. "Level 1", "Level 2") for one analyte (lab_test_parameter),
 * with the manufacturer/lab-established target mean+SD used to compute
 * each run's z-score.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_qc_lots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('lab_test_parameter_id')->index();
            $table->string('lot_number');
            $table->string('level', 32)->default('Level 1');
            $table->decimal('target_mean', 12, 4);
            $table->decimal('target_sd', 12, 4);
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('lab_test_parameter_id', 'qc_lots_parameter_fk')->references('id')->on('lab_test_parameters')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_qc_lots');
    }
};
