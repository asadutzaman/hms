<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('er_triages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('er_visit_id')->constrained('er_visits')->cascadeOnDelete();

            $table->tinyInteger('triage_level'); // 1 (most acute) .. 5 (least acute)
            $table->string('color_band', 10); // red, orange, yellow, green, blue
            $table->integer('target_minutes'); // target time-to-be-seen for this level

            $table->smallInteger('bp_systolic')->nullable();
            $table->smallInteger('bp_diastolic')->nullable();
            $table->smallInteger('pulse_bpm')->nullable();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->smallInteger('spo2_pct')->nullable();
            $table->smallInteger('respiratory_rate')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('triaged_by')->nullable();
            $table->timestamp('triaged_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['er_visit_id', 'triaged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('er_triages');
    }
};
