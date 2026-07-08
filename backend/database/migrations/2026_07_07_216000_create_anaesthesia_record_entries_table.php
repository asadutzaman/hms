<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anaesthesia_record_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('anaesthesia_record_id')->index();
            $table->dateTime('recorded_at')->index();

            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('bp_systolic')->nullable();
            $table->unsignedSmallInteger('bp_diastolic')->nullable();
            $table->unsignedSmallInteger('spo2_pct')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_dose')->nullable();
            $table->string('fluids_given')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('anaesthesia_record_id', 'anaes_entries_record_fk')->references('id')->on('anaesthesia_records')->onDelete('cascade');
            $table->foreign('recorded_by', 'anaes_entries_recorded_by_fk')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anaesthesia_record_entries');
    }
};
