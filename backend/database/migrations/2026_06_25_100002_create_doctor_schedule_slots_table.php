<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorScheduleSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('doctor_schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            $table->foreignId('doctor_schedule_id')->constrained('doctor_schedules')->cascadeOnDelete();

            // Day of week 0 (Sunday) … 6 (Saturday) – matches Carbon's dayOfWeek
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');

            // Optional override of schedule-level slot capacity for this window
            $table->unsignedSmallInteger('slot_duration_minutes')->nullable();
            $table->unsignedSmallInteger('max_patients_per_slot')->nullable();

            $table->string('session_label', 50)->nullable(); // e.g. Morning, Evening
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            // Optimised for weekly recurrence lookup
            $table->index(['doctor_schedule_id', 'day_of_week'], 'idx_doc_slot_schedule_day');
            $table->index(['day_of_week', 'is_active'], 'idx_doc_slot_day_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_schedule_slots');
    }
}
