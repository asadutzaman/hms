<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('ward_id')->index();
            $table->string('bed_number', 32);
            $table->string('bed_type', 32)->nullable();
            $table->decimal('daily_rate', 15, 4)->default(0);
            $table->string('bed_status', 32)->default('vacant')->index();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            $table->unique(['ward_id', 'bed_number'], 'beds_ward_bed_number_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
