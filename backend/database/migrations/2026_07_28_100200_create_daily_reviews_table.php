<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ward-doctor WD2 "Daily review" — one progress note per inpatient per round. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('ipd_admission_id')->index();
            $table->unsignedBigInteger('author_user_id')->nullable();
            $table->date('review_date')->nullable();

            $table->text('progress_note')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->json('obs_snapshot')->nullable();

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
        Schema::dropIfExists('daily_reviews');
    }
};
