<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Single audit trail for the whole admission episode — admit, bed transfers,
 * discharge/dama/deceased, discount workflow, and payments all log here
 * (action-specific detail goes in `payload`). Deliberately no separate
 * ipd_bed_transfers table: two disconnected audit trails for one episode
 * was flagged as a foreseeable reporting gap during design.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_admission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('ipd_admission_id')->constrained('ipd_admissions')->cascadeOnDelete();

            $table->string('action', 40);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();

            $table->unsignedBigInteger('actor_id')->nullable()->index();

            $table->json('payload')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('occurred_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['ipd_admission_id', 'created_at'], 'idx_ipd_adm_audit_admission_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_admission_audit_logs');
    }
};
