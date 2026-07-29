<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_blue_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            // Shared by Doctor "Code Blue" (D6) and Nurse "Rapid response" (N9).
            $table->string('event_type', 30)->default('code_blue'); // code_blue | rapid_response
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('ward_id')->nullable()->index();
            $table->unsignedBigInteger('bed_id')->nullable();
            $table->string('location', 150)->nullable();

            // Workflow state — deliberately NOT named "status" (reserved as the
            // boolean soft-active flag per project convention).
            $table->string('state', 20)->default('active'); // active | responded | resolved | cancelled
            $table->string('severity', 20)->nullable();
            $table->text('reason')->nullable();
            $table->json('responders')->nullable();
            $table->text('outcome_notes')->nullable();

            $table->unsignedBigInteger('raised_by')->nullable();
            $table->timestamp('raised_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

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
        Schema::dropIfExists('code_blue_events');
    }
};
