<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Nurse N11 "Discharge checklist" — one per admission; items held as JSON. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discharge_checklists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('ipd_admission_id')->index();
            // [{key, label, checked, checked_by, checked_at, note}]
            $table->json('items')->nullable();
            $table->string('state', 20)->default('in_progress'); // in_progress | complete
            $table->timestamp('completed_at')->nullable();

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
        Schema::dropIfExists('discharge_checklists');
    }
};
