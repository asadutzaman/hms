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
        // opd_prescription_items.opd_visit_id is referenced by the model,
        // validator, and OpdPrescriptionRepository::saveForVisit() but was
        // never added to the original migration — every prescription save
        // has been failing with "column does not exist" (pre-existing
        // model/migration drift, same class of bug as the other OPD tables).
        Schema::table('opd_prescription_items', function (Blueprint $table) {
            $table->foreignId('opd_visit_id')->nullable()->after('opd_prescription_id')->constrained('opd_visits')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd_prescription_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('opd_visit_id');
        });
    }
};
