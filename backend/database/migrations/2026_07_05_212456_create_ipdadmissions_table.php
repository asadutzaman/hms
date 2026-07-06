<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_admissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('admission_no', 64)->unique();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('opd_visit_id')->nullable()->index();
            $table->string('admission_type', 16)->default('emergency')->index();
            $table->unsignedBigInteger('attending_doctor_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('ward_id')->index();
            $table->unsignedBigInteger('bed_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();

            $table->dateTime('admission_date');
            $table->date('expected_discharge_date')->nullable();
            $table->dateTime('discharge_date')->nullable();
            $table->string('admission_status', 16)->default('admitted')->index();

            $table->text('diagnosis_at_admission')->nullable();
            $table->text('discharge_override_reason')->nullable();

            $table->unsignedBigInteger('admitted_by')->nullable();
            $table->unsignedBigInteger('discharged_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('restrict');
            $table->foreign('department_id', 'ipd_admissions_department_id_fk')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_admissions');
    }
};
