<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorScheduleExceptionsTable extends Migration
{
    public function up()
    {
        Schema::create('doctor_schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // employees table pending; FK added later by add_appointment_doctor_fks migration
            $table->unsignedBigInteger('doctor_id');
            $table->foreignId('doctor_schedule_id')->nullable()->constrained('doctor_schedules')->nullOnDelete();

            $table->date('exception_date');
            $table->enum('exception_type', ['leave', 'holiday', 'extra_slot', 'blocked'])->default('leave');

            // For leave/blocked – leave start/end blank
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();

            // Status workflow for leave approval
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'cancelled'])->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['doctor_id', 'exception_date'], 'idx_doc_exc_doctor_date');
            $table->index(['exception_date', 'exception_type'], 'idx_doc_exc_date_type');
            $table->index(['approval_status', 'status'], 'idx_doc_exc_approval');
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_schedule_exceptions');
    }
}
