<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_nursing_assessments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('admission_id')->unique()->constrained('ipd_admissions')->cascadeOnDelete();

            $table->text('general_appearance')->nullable();
            $table->string('mobility_status', 50)->nullable();
            $table->smallInteger('fall_risk_score')->nullable();
            $table->string('fall_risk_level', 10)->nullable();
            $table->smallInteger('pressure_injury_risk_score')->nullable();
            $table->string('pressure_injury_risk_level', 10)->nullable();
            $table->text('pain_assessment')->nullable();
            $table->text('nutrition_risk')->nullable();
            $table->text('skin_integrity_notes')->nullable();
            $table->text('psychosocial_notes')->nullable();
            $table->text('care_plan_notes')->nullable();

            $table->unsignedBigInteger('assessed_by')->nullable();
            $table->timestamp('assessed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_nursing_assessments');
    }
};
