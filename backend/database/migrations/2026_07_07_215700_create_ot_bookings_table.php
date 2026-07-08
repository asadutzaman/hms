<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-09-01 OT Scheduling & Booking. ipd_admission_id is nullable — day-case/
 * minor surgery may not require a formal ward admission, but when the
 * patient is admitted (the common case per the F-04-02 dependency) it links
 * back to that admission. equipment_list is a simple JSON array (no
 * dedicated equipment master/catalog — the acceptance criteria only asks
 * for "equipment list", not equipment inventory tracking).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('booking_no', 64)->unique();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('ipd_admission_id')->nullable()->index();
            $table->unsignedBigInteger('theatre_id')->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('surgeon_id')->index();
            $table->unsignedBigInteger('anaesthetist_id')->nullable()->index();

            $table->string('surgery_name');
            $table->string('surgery_type', 16)->default('elective')->index();
            $table->date('scheduled_date')->index();
            $table->time('scheduled_start_time');
            $table->time('scheduled_end_time');
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->json('equipment_list')->nullable();
            $table->string('booking_status', 16)->default('scheduled')->index();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('booked_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('ipd_admission_id', 'ot_bookings_admission_fk')->references('id')->on('ipd_admissions')->onDelete('set null');
            $table->foreign('theatre_id')->references('id')->on('theatres')->onDelete('restrict');
            $table->foreign('department_id', 'ot_bookings_department_fk')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('surgeon_id', 'ot_bookings_surgeon_fk')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('anaesthetist_id', 'ot_bookings_anaesthetist_fk')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_bookings');
    }
};
