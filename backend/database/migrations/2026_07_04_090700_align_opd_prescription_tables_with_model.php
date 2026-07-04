<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * OpdPrescription/OpdPrescriptionItem models, validators, and
 * OpdPrescriptionRepository::saveForVisit() were written against a richer
 * column set than the original migrations created — every prescription save
 * was failing with "column does not exist". Align the tables with the code.
 */
class AlignOpdPrescriptionTablesWithModel extends Migration
{
    public function up()
    {
        Schema::table('opd_prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('opd_visit_id');
            $table->date('follow_up_date')->nullable()->after('advice');
            $table->boolean('is_printed')->default(false)->after('notes');
            $table->timestamp('printed_at')->nullable()->after('is_printed');
        });

        $itemsTable = DB::getTablePrefix() . 'opd_prescription_items';
        DB::statement("ALTER TABLE {$itemsTable} DROP CONSTRAINT IF EXISTS {$itemsTable}_frequency_check");
        DB::statement("ALTER TABLE {$itemsTable} DROP CONSTRAINT IF EXISTS {$itemsTable}_route_check");

        Schema::table('opd_prescription_items', function (Blueprint $table) {
            $table->renameColumn('instructions', 'instruction');
        });

        Schema::table('opd_prescription_items', function (Blueprint $table) {
            $table->string('generic_name', 200)->nullable()->after('drug_name');
            $table->string('strength', 50)->nullable()->after('generic_name');
            $table->string('dosage_form', 50)->nullable()->after('strength');
            $table->decimal('dose_value', 8, 3)->nullable()->after('dosage_form');
            $table->string('dose_unit', 20)->nullable()->after('dose_value');
            $table->string('frequency', 10)->default('OD')->change();
            $table->unsignedSmallInteger('duration_value')->nullable()->after('duration_days');
            $table->string('duration_unit', 10)->default('days')->after('duration_value');
            $table->string('route', 20)->default('oral')->change();
            $table->boolean('is_prn')->default(false)->after('instruction');
            $table->decimal('unit_price', 10, 2)->default(0)->after('is_prn');
            $table->decimal('amount', 10, 2)->default(0)->after('unit_price');
        });
    }

    public function down()
    {
        Schema::table('opd_prescriptions', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'follow_up_date', 'is_printed', 'printed_at']);
        });

        Schema::table('opd_prescription_items', function (Blueprint $table) {
            $table->dropColumn(['generic_name', 'strength', 'dosage_form', 'dose_value', 'dose_unit', 'duration_value', 'duration_unit', 'is_prn', 'unit_price', 'amount']);
            $table->renameColumn('instruction', 'instructions');
        });
    }
}
