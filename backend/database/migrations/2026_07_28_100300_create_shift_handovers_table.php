<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** End-of-shift handover (SBAR) — shared by ward-doctor (DD7) and nurse (N4). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('role_type', 20)->default('doctor'); // doctor | nurse
            $table->unsignedBigInteger('ward_id')->nullable()->index();
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->unsignedBigInteger('to_user_id')->nullable();
            $table->string('shift_label', 60)->nullable();

            $table->text('summary')->nullable();
            // SBAR watch-list: [{patient_id, situation, background, assessment, recommendation, priority}]
            $table->json('items')->nullable();

            $table->string('state', 20)->default('draft'); // draft | submitted | accepted
            $table->timestamp('handed_over_at')->nullable();

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
        Schema::dropIfExists('shift_handovers');
    }
};
