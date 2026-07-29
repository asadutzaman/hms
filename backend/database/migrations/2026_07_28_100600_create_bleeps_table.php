<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** On-call DD3 "Incoming bleep" — escalation pages to the on-call holder. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bleeps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->unsignedBigInteger('to_user_id')->nullable()->index();
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->string('callback', 60)->nullable();
            $table->string('priority', 20)->default('routine'); // routine | urgent | crash
            $table->text('message')->nullable();

            $table->string('state', 20)->default('sent'); // sent | acknowledged | escalated | closed
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('escalated_at')->nullable();

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
        Schema::dropIfExists('bleeps');
    }
};
