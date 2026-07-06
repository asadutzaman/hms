<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_fluid_balances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('admission_id')->constrained('ipd_admissions')->cascadeOnDelete();

            $table->string('balance_type', 10); // intake | output
            $table->string('category', 30); // oral, iv, ng_tube, urine, vomitus, drain, stool, other
            $table->decimal('amount_ml', 8, 2);
            $table->string('shift', 10)->nullable(); // morning | evening | night
            $table->dateTime('recorded_at');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['admission_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_fluid_balances');
    }
};
