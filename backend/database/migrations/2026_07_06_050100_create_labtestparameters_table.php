<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_test_parameters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('lab_test_id')->constrained('lab_tests')->cascadeOnDelete();

            $table->string('parameter_name');
            $table->string('unit', 30)->nullable();
            $table->string('result_data_type', 10)->default('numeric'); // numeric, text, select
            $table->text('select_options')->nullable(); // comma-separated, when result_data_type = select
            $table->decimal('critical_low', 12, 4)->nullable();
            $table->decimal('critical_high', 12, 4)->nullable();
            $table->unsignedSmallInteger('sequence')->default(1);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['lab_test_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_test_parameters');
    }
};
