<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** On-call DD4 "A to E assessment" — Airway/Breathing/Circulation/Disability/Exposure + NEWS2. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atoe_assessments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('ipd_admission_id')->nullable()->index();
            $table->unsignedBigInteger('assessed_by')->nullable();
            $table->timestamp('assessed_at')->nullable();

            $table->text('airway')->nullable();
            $table->text('breathing')->nullable();
            $table->text('circulation')->nullable();
            $table->text('disability')->nullable();
            $table->text('exposure')->nullable();
            $table->smallInteger('news2_score')->nullable();
            $table->text('impression')->nullable();
            $table->text('plan')->nullable();

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
        Schema::dropIfExists('atoe_assessments');
    }
};
