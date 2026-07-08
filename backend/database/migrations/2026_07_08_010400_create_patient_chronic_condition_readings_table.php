<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_chronic_condition_readings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('condition_id')->index();
            $table->date('reading_date')->index();
            $table->string('reading_type', 32);
            $table->string('reading_value', 64);
            $table->string('unit', 32)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('condition_id', 'chronic_readings_condition_fk')->references('id')->on('patient_chronic_conditions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_chronic_condition_readings');
    }
};
