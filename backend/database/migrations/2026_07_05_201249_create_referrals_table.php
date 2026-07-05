<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_visit_id')->constrained('opd_visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('referring_doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('referred_to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('referred_to_doctor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('external_facility_name')->nullable()->comment('set for referrals outside this facility');
            $table->text('reason');
            $table->enum('urgency', ['routine', 'urgent', 'emergency'])->default('routine');
            $table->enum('referral_status', ['pending', 'accepted', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index('opd_visit_id', 'referrals_opd_visit_id_idx');
            $table->index('patient_id', 'referrals_patient_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
