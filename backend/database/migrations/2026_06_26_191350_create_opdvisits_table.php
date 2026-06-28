<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdvisitsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            // Identification
            $table->string('opd_no', 30)->unique()->index();

            // Foreign keys (employees FKs added later via idempotent migration)
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->unsignedBigInteger('doctor_id')->index();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // Visit type & timing
            $table->enum('visit_type', ['appointment', 'walk_in'])->default('walk_in');
            $table->date('visit_date')->index();
            $table->integer('token_number')->nullable();

            // Status
            $table->enum('status', [
                'waiting', 'vitals_taken', 'in_consultation',
                'completed', 'billed', 'closed', 'cancelled'
            ])->default('waiting')->index();

            // Clinical fields
            $table->text('chief_complaint')->nullable();
            $table->text('history')->nullable();
            $table->text('examination')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->text('advice')->nullable();

            // Timestamps
            $table->timestamp('consultation_start_at')->nullable();
            $table->timestamp('consultation_end_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Audit / cancellation
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();

            // Standard audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();

            // Composite indexes
            $table->index(['doctor_id', 'visit_date'], 'idx_opd_visits_doctor_date');
            $table->index(['patient_id', 'visit_date'], 'idx_opd_visits_patient_date');
            $table->index(['status', 'visit_date'], 'idx_opd_visits_status_date');
            $table->index(['department_id', 'visit_date'], 'idx_opd_visits_dept_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_visits');
    }
}
