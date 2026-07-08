<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_cross_matches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('blood_unit_id')->index();
            $table->string('patient_blood_group', 4)->nullable();
            $table->string('cross_match_result', 16)->default('pending')->index();
            $table->string('method', 32)->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->dateTime('performed_at')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id', 'cross_matches_patient_fk')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('blood_unit_id', 'cross_matches_unit_fk')->references('id')->on('blood_units')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_cross_matches');
    }
};
