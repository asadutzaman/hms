<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // employees table pending; FK added later by add_appointment_doctor_fks migration
            $table->unsignedBigInteger('doctor_id');
            $table->foreignId('doctor_schedule_id')->nullable()->constrained('doctor_schedules')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('chamber_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamp('slot_start_at')->nullable();
            $table->timestamp('slot_end_at')->nullable();

            $table->unsignedSmallInteger('max_patients')->default(1);
            $table->unsignedSmallInteger('booked_count')->default(0);
            $table->unsignedSmallInteger('hold_count')->default(0);
            $table->unsignedSmallInteger('walk_in_count')->default(0);
            $table->unsignedSmallInteger('waitlist_count')->default(0);

            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason', 255)->nullable();
            $table->boolean('is_extra_slot')->default(false);

            $table->enum('status', ['open', 'full', 'blocked', 'closed'])->default('open');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_active')->default(1);
            $table->softDeletes();
            $table->timestamps();

            // Hot-path indexes for slot availability search
            $table->unique(['doctor_id', 'slot_date', 'start_time'], 'uniq_appt_slot_doctor_time');
            $table->index(['slot_date', 'status'], 'idx_appt_slot_date_status');
            $table->index(['doctor_id', 'slot_date'], 'idx_appt_slot_doctor_date');
            $table->index(['department_id', 'slot_date'], 'idx_appt_slot_dept_date');
            $table->index(['slot_start_at', 'status'], 'idx_appt_slot_start_status');
            $table->index(['is_blocked', 'status'], 'idx_appt_slot_block');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_slots');
    }
}
