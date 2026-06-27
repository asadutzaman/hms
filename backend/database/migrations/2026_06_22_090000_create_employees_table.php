<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal `employees` table for Sprint 3 (OPD) until the full Employee Master
 * module from Sprint 1 lands.
 *
 * Columns mirror the Employee model's $fillable (plus the Autofill / SoftDeletes
 * / Uuid trait defaults). No FKs out of this table -- it is the parent for
 * doctor_id columns on appointments + OPD tables; those FKs are attached
 * separately by:
 *   - 2026_06_25_100009_add_appointment_doctor_fks_when_employees_exists.php
 *   - 2026_06_26_200001_add_opd_employee_fks_when_employees_exists.php
 *
 * Idempotent: skips up() if the table already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');

            // organisational context (nullable so a bare-bones row is creatable)
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('organogram_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // employee identity
            $table->string('employee_id', 64)->nullable()->unique();
            $table->string('name_en')->nullable();
            $table->string('name_bn')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('gender', 16)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('religion', 32)->nullable();

            // employment classification
            $table->string('employee_type', 32)->nullable()->index();
            $table->unsignedBigInteger('designation_id')->nullable()->index();
            $table->string('employee_category', 32)->nullable();
            $table->string('employee_class', 32)->nullable();

            // dates
            $table->date('dob')->nullable();
            $table->date('joining_date')->nullable();

            // media
            $table->string('image')->nullable();
            $table->string('e_signature')->nullable();

            // imake:crud defaults
            $table->integer('status')->default(1)->index();
            $table->integer('sort_order')->default(0);

            // Autofill + SoftDeletes + Uuid
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('uuid', 64)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};