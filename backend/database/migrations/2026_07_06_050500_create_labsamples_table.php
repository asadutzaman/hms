<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();

            $table->string('barcode', 40)->unique();
            $table->string('sample_type', 50);
            $table->string('sample_status', 20)->default('pending_collection')->index();
            // pending_collection -> collected -> received ; rejected is terminal

            $table->unsignedBigInteger('collected_by')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['lab_order_id', 'sample_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_samples');
    }
};
