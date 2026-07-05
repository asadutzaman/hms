<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('drug_interactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('drug_a_id')->constrained('drugs')->cascadeOnDelete();
            $table->foreignId('drug_b_id')->constrained('drugs')->cascadeOnDelete();
            $table->enum('severity', ['minor', 'moderate', 'severe', 'contraindicated'])->default('moderate');
            $table->text('description')->nullable();
            $table->text('recommendation')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->unique(['drug_a_id', 'drug_b_id'], 'drug_interactions_pair_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_interactions');
    }
};
