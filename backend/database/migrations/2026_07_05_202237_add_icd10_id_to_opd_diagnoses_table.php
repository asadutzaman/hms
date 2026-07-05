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
        Schema::table('opd_diagnoses', function (Blueprint $table) {
            $table->foreignId('icd10_id')->nullable()->after('diagnosis_type')->constrained('icd10_codes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd_diagnoses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('icd10_id');
        });
    }
};
