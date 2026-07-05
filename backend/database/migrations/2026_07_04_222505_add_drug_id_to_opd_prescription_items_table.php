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
        Schema::table('opd_prescription_items', function (Blueprint $table) {
            $table->foreignId('drug_id')->nullable()->after('opd_visit_id')->constrained('drugs')->nullOnDelete();
            $table->decimal('dispensed_quantity', 8, 3)->default(0)->after('dose_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd_prescription_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('drug_id');
            $table->dropColumn('dispensed_quantity');
        });
    }
};
