<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prescription_template_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('prescription_template_id')->constrained('prescription_templates')->cascadeOnDelete();
            $table->foreignId('drug_id')->nullable()->constrained('drugs')->nullOnDelete();
            $table->string('drug_name');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('dosage_form')->nullable();
            $table->decimal('dose_value', 8, 3)->nullable();
            $table->string('dose_unit', 20)->nullable();
            $table->string('frequency', 10)->default('OD');
            $table->unsignedSmallInteger('duration_value')->nullable();
            $table->string('duration_unit', 10)->default('days');
            $table->string('route', 20)->default('oral');
            $table->string('instruction', 500)->nullable();
            $table->boolean('is_prn')->default(false);
            $table->integer('sequence')->default(1);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index('prescription_template_id', 'pti_template_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_template_items');
    }
};
