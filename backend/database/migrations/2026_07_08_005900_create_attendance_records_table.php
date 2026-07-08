<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-13-02 Attendance Management. "Biometric punches synced" — there is no
 * real biometric device/hardware to integrate with in this project; source
 * distinguishes manual staff entry from punches pushed in via the same
 * shape a biometric device integration would use (AttendanceController::sync),
 * documented as the actual integration point rather than pretending to talk
 * to real hardware.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('employee_id')->index();
            $table->date('attendance_date')->index();
            $table->unsignedBigInteger('shift_id')->nullable()->index();

            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->string('source', 16)->default('manual');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->unique(['employee_id', 'attendance_date'], 'attendance_employee_date_unique');
            $table->foreign('employee_id', 'attendance_employee_fk')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('shift_id', 'attendance_shift_fk')->references('id')->on('shifts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
