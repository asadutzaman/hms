<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('appointment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            // employees table pending; FK added later by add_appointment_doctor_fks migration
            $table->unsignedBigInteger('doctor_id')->nullable();

            $table->string('action', 60); // created, confirmed, rescheduled, cancelled, checked_in, completed, no_show, notified, payment_updated …
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();

            $table->json('payload')->nullable();
            $table->text('remarks')->nullable();

            $table->string('actor_type', 30)->default('user'); // user, system, patient
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamp('occurred_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['appointment_id', 'occurred_at'], 'idx_appt_audit_appt');
            $table->index(['action', 'occurred_at'], 'idx_appt_audit_action');
            $table->index(['doctor_id', 'occurred_at'], 'idx_appt_audit_doctor');
            $table->index(['patient_id', 'occurred_at'], 'idx_appt_audit_patient');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_audit_logs');
    }
}
