<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-05-10 — one row per QC run, feeding the Levey-Jennings chart
 * (run_date on the x-axis, z_score / measured_value on the y-axis).
 * is_out_of_control uses a simple 1-3s Westgard rule (|z| > 3) — a full
 * multi-rule Westgard engine (1-2s warning, 2-2s, R-4s, 4-1s, 10x) is out
 * of scope for this sprint's SP budget.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_qc_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('qc_lot_id')->index();
            $table->dateTime('run_date')->index();
            $table->decimal('measured_value', 12, 4);
            $table->decimal('z_score', 8, 2)->nullable();
            $table->boolean('is_out_of_control')->default(false);
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('qc_lot_id', 'qc_runs_lot_fk')->references('id')->on('lab_qc_lots')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_qc_runs');
    }
};
