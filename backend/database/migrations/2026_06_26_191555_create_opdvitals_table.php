<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdvitalsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_vitals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_visit_id')->unique()->constrained('opd_visits')->cascadeOnDelete();

            // Vitals
            $table->unsignedSmallInteger('systolic')->nullable();   // mmHg
            $table->unsignedSmallInteger('diastolic')->nullable();  // mmHg
            $table->unsignedSmallInteger('pulse')->nullable();      // bpm
            $table->decimal('temperature', 4, 1)->nullable();      // °C
            $table->unsignedSmallInteger('spo2')->nullable();       // %
            $table->decimal('weight', 5, 2)->nullable();           // kg
            $table->decimal('height', 5, 2)->nullable();           // cm
            $table->decimal('bmi', 5, 2)->nullable();              // kg/m²
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamp('recorded_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_vitals');
    }
}
