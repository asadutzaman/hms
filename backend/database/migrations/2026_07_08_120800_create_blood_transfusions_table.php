<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-11-03 Cross Matching & Transfusion Record — "transfusion unit linked
 * to patient": blood_unit_id is unique here since one unit can only ever
 * be transfused into one patient (enforced at the DB level, not just in
 * the service).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_transfusions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('blood_unit_id')->unique();
            $table->unsignedBigInteger('cross_match_id')->nullable()->index();
            $table->unsignedBigInteger('ipd_admission_id')->nullable()->index();

            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->boolean('reaction_observed')->default(false);
            $table->text('reaction_notes')->nullable();
            $table->unsignedBigInteger('administered_by')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id', 'transfusions_patient_fk')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('blood_unit_id', 'transfusions_unit_fk')->references('id')->on('blood_units')->onDelete('restrict');
            $table->foreign('cross_match_id', 'transfusions_cross_match_fk')->references('id')->on('blood_cross_matches')->onDelete('set null');
            $table->foreign('ipd_admission_id', 'transfusions_admission_fk')->references('id')->on('ipd_admissions')->onDelete('set null');
            $table->foreign('administered_by', 'transfusions_administered_by_fk')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_transfusions');
    }
};
