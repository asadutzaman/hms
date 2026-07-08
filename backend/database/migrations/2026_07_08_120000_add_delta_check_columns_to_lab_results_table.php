<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-05-09 Delta Check Validation — computed at result-entry time by
 * LabResultService against the patient's most recent prior result for the
 * same lab_test_parameter_id, then stored (not just shown live) so it's
 * visible on the report/history afterwards too. F-05-06 Machine
 * Interfacing — result_source distinguishes analyzer-imported results from
 * manual technician entry (manual override just re-saves the same row via
 * the existing updateOrCreate in enterResults()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->string('result_source', 16)->default('manual')->after('remarks');
            $table->string('previous_value_snapshot', 255)->nullable()->after('result_source');
            $table->decimal('delta_percent', 8, 2)->nullable()->after('previous_value_snapshot');
            $table->boolean('delta_flag')->default(false)->after('delta_percent');
        });
    }

    public function down(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropColumn(['result_source', 'previous_value_snapshot', 'delta_percent', 'delta_flag']);
        });
    }
};
