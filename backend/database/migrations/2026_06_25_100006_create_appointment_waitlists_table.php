<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentWaitlistsTable extends Migration
{
    public function up()
    {
        Schema::create('appointment_waitlists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            // employees table pending; FK added later by add_appointment_doctor_fks migration
            $table->unsignedBigInteger('doctor_id');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('preferred_slot_id')->nullable()->constrained('appointment_slots')->nullOnDelete();
            $table->foreignId('converted_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();

            // Desired date window
            $table->date('preferred_date_from');
            $table->date('preferred_date_to');

            // Time-of-day preference
            $table->enum('time_preference', ['morning', 'afternoon', 'evening', 'any'])->default('any');

            $table->unsignedSmallInteger('priority')->default(5); // 1=highest
            $table->unsignedInteger('queue_position')->default(0);

            $table->enum('status', [
                'waiting',     // active
                'notified',    // patient informed a slot opened
                'converted',   // promoted to appointment
                'expired',
                'cancelled',
            ])->default('waiting');

            $table->timestamp('notified_at')->nullable();
            $table->timestamp('notification_expires_at')->nullable();
            $table->unsignedTinyInteger('notification_attempts')->default(0);

            $table->string('reason_for_visit', 255)->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_active')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['doctor_id', 'status', 'priority'], 'idx_waitlist_doctor_status_pri');
            $table->index(['patient_id', 'status'], 'idx_waitlist_patient_status');
            $table->index(['preferred_date_from', 'preferred_date_to'], 'idx_waitlist_window');
            $table->index(['status', 'notification_expires_at'], 'idx_waitlist_expiry');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_waitlists');
    }
}
