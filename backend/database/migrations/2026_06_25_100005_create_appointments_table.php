<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // Human-friendly identifier (APT-2026-00001)
            $table->string('appointment_no', 30)->unique()->index();

            // Patient / Doctor
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            // employees table pending; FK added later by add_appointment_doctor_fks migration
            $table->unsignedBigInteger('doctor_id');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('chamber_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('appointment_slot_id')->nullable()->constrained('appointment_slots')->nullOnDelete();

            // Source: online, walk-in, phone, follow-up, referral
            $table->enum('source', ['online', 'walk_in', 'phone', 'follow_up', 'referral', 'staff'])->default('online');
            $table->enum('consultation_mode', ['in_person', 'telemedicine'])->default('in_person');

            // Scheduling
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamp('appointment_at')->nullable();

            // Token / queue (walk-in flow)
            $table->unsignedInteger('token_number')->nullable();
            $table->unsignedInteger('token_sequence')->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',          // awaiting payment/confirm
                'confirmed',        // booked
                'checked_in',       // arrived at counter
                'in_consultation',  // with doctor
                'completed',        // done
                'cancelled',
                'no_show',
                'rescheduled',
                'waitlisted',
                'expired',
            ])->default('confirmed');

            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded', 'waived'])->default('unpaid');
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('currency', 5)->default('BDT');

            // Reason / notes
            $table->string('reason_for_visit', 255)->nullable();
            $table->text('symptoms')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Reschedule chain
            $table->foreignId('rescheduled_from_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->unsignedTinyInteger('reschedule_count')->default(0);

            // Confirmation / check-in timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('consultation_started_at')->nullable();
            $table->timestamp('consultation_ended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Foreign references
            $table->foreignId('booked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            // Standard columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_active')->default(1);
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['patient_id', 'appointment_date'], 'idx_appt_patient_date');
            $table->index(['doctor_id', 'appointment_date', 'status'], 'idx_appt_doctor_date_status');
            $table->index(['department_id', 'appointment_date'], 'idx_appt_dept_date');
            $table->index(['status', 'appointment_date'], 'idx_appt_status_date');
            $table->index(['source', 'status'], 'idx_appt_source_status');
            $table->index(['appointment_at', 'status'], 'idx_appt_at_status');
            $table->index(['appointment_slot_id', 'status'], 'idx_appt_slot_status');
            $table->index(['token_number', 'appointment_date'], 'idx_appt_token_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
