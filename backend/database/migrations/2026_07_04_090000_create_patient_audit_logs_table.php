<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('patient_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->unsignedBigInteger('merged_into_patient_id')->nullable();

            $table->string('action', 60); // create, update, delete, merged, merged_away

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('remarks')->nullable();

            $table->string('actor_type', 30)->default('user');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamp('occurred_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['patient_id', 'occurred_at'], 'idx_patient_audit_patient');
            $table->index(['action', 'occurred_at'], 'idx_patient_audit_action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_audit_logs');
    }
}
