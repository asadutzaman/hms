<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // doctor_id: employees table is pending (Employee Master is Sprint 1 follow-up).
            // Use unsignedBigInteger now; FK will be added in a later migration via add_appointment_doctor_fks migration.
            $table->unsignedBigInteger('doctor_id');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('chamber_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->string('name', 150)->nullable();
            $table->enum('schedule_type', ['regular', 'on_call', 'consultation', 'procedure'])->default('regular');
            $table->enum('consultation_mode', ['in_person', 'telemedicine', 'both'])->default('in_person');

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->unsignedSmallInteger('slot_duration_minutes')->default(15);
            $table->unsignedSmallInteger('max_patients_per_slot')->default(1);
            $table->unsignedSmallInteger('buffer_minutes')->default(0);

            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->decimal('follow_up_fee', 10, 2)->default(0);
            $table->string('currency', 5)->default('BDT');

            $table->boolean('is_recurring')->default(true);
            $table->boolean('allow_online_booking')->default(true);
            $table->boolean('allow_walk_in')->default(true);
            $table->boolean('is_default')->default(false);

            $table->text('notes')->nullable();

            // Standard audit columns (matching Patient/Department/Designation)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();

            // Indexes optimised for availability lookup
            $table->index(['doctor_id', 'effective_from', 'effective_to'], 'idx_doc_schedule_window');
            $table->index(['department_id', 'status'], 'idx_doc_schedule_dept');
            $table->index(['schedule_type', 'status'], 'idx_doc_schedule_type');
            $table->index(['is_default', 'doctor_id'], 'idx_doc_schedule_default');
            $table->index(['status', 'deleted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_schedules');
    }
}
