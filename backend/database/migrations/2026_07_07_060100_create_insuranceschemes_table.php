<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_schemes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('insurance_company_id')->index();
            $table->string('name');
            $table->decimal('coverage_percent', 5, 2)->default(100);
            $table->decimal('max_limit', 14, 2)->nullable();
            $table->text('covered_services')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('insurance_company_id')->references('id')->on('insurance_companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_schemes');
    }
};
