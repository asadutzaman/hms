<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('order_id')->constrained('ipd_medication_orders')->cascadeOnDelete();

            $table->dateTime('scheduled_at')->nullable(); // null for ad-hoc PRN administrations
            $table->dateTime('administered_at')->nullable();
            $table->string('administration_status', 20)->default('scheduled')->index(); // scheduled, given, held, refused, missed

            $table->unsignedBigInteger('administered_by')->nullable();
            $table->unsignedBigInteger('witnessed_by')->nullable(); // controlled-drug co-sign, mirrors ControlledDrugRegister
            $table->string('reason', 255)->nullable(); // why held/refused
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['order_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_medication_administrations');
    }
};
