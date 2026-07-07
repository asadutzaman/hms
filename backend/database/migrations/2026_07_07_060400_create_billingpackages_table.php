<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('package_type', 10)->default('ipd')->index(); // opd, ipd, both
            $table->decimal('fixed_price', 14, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();

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
        Schema::dropIfExists('billing_packages');
    }
};
