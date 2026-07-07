<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_test_reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('lab_test_parameter_id')->constrained('lab_test_parameters')->cascadeOnDelete();

            $table->string('gender', 10)->default('all'); // all, male, female
            $table->unsignedSmallInteger('age_min_years')->default(0);
            $table->unsignedSmallInteger('age_max_years')->nullable(); // null = no upper bound

            $table->decimal('range_low', 12, 4)->nullable();
            $table->decimal('range_high', 12, 4)->nullable();
            $table->string('range_text', 255)->nullable(); // for non-numeric expected values, e.g. "Negative"

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['lab_test_parameter_id', 'gender', 'age_min_years']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_test_reference_ranges');
    }
};
