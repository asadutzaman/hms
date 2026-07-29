<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** On-call DD5 "Order sets" — named bundles of orders; items held as JSON. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_sets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('name', 150);
            $table->string('category', 60)->nullable();
            $table->text('description')->nullable();
            // [{type: lab|radiology|medication, ref_id, name, meta:{}}]
            $table->json('items')->nullable();
            $table->boolean('is_global')->default(true);
            $table->unsignedBigInteger('owner_user_id')->nullable();

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
        Schema::dropIfExists('order_sets');
    }
};
