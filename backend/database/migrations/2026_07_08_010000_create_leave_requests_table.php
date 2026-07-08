<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-13-03 Leave Management. process_status is the generic workflow
 * engine's expected field name (DRAFT/SUBMITTED/APPROVED/REJECTED/
 * BACKWARD_INITIATION) — same 2-step Initiation->Approval wiring as
 * GoodsReceiveNote (see project_hms_workflow_engine_and_scaffolding_quirks
 * memory), not a bespoke leave-specific approval mechanism.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('request_no', 64)->unique();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedBigInteger('leave_type_id')->index();

            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 5, 2);
            $table->text('reason')->nullable();

            $table->string('process_status', 32)->default('DRAFT')->index();

            $table->unsignedBigInteger('applied_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('employee_id', 'leave_requests_employee_fk')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_type_id', 'leave_requests_type_fk')->references('id')->on('leave_types')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
