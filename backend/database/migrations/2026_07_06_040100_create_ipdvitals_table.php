<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_vitals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('admission_id')->constrained('ipd_admissions')->cascadeOnDelete();

            $table->dateTime('recorded_at');
            $table->unsignedBigInteger('recorded_by')->nullable();

            $table->smallInteger('bp_systolic')->nullable();
            $table->smallInteger('bp_diastolic')->nullable();
            $table->smallInteger('pulse_bpm')->nullable();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->string('temperature_method', 20)->nullable();
            $table->smallInteger('spo2_pct')->nullable();
            $table->smallInteger('respiratory_rate')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('height_cm', 6, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->decimal('blood_glucose_mg_dl', 6, 2)->nullable();
            $table->tinyInteger('pain_score')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['admission_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_vitals');
    }
};
