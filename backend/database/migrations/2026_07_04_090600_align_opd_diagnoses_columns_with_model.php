<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * OpdDiagnosis's model/validator expect `diagnosis_name` (required),
 * `icd10_description`, `patient_id`, `is_primary`/`is_chronic`/`is_confirmed`,
 * and `recorded_by`, plus a `diagnosis_type` that also allows
 * differential/final — none of which the original migration created (it only
 * had `description` and a 2-value diagnosis_type check constraint). Every
 * diagnosis save was failing with "column does not exist" or a check
 * constraint violation. Align the table with the code.
 */
class AlignOpdDiagnosesColumnsWithModel extends Migration
{
    public function up()
    {
        $table = DB::getTablePrefix() . 'opd_diagnoses';
        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_diagnosis_type_check");

        Schema::table('opd_diagnoses', function (Blueprint $table) {
            $table->renameColumn('description', 'diagnosis_name');
            $table->string('icd10_code', 20)->nullable()->change();
        });

        Schema::table('opd_diagnoses', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('opd_visit_id');
            $table->string('icd10_description', 500)->nullable()->after('icd10_code');
            $table->string('diagnosis_type', 20)->default('primary')->change();
            $table->boolean('is_primary')->default(false)->after('diagnosis_name');
            $table->boolean('is_chronic')->default(false)->after('is_primary');
            $table->boolean('is_confirmed')->default(false)->after('is_chronic');
            $table->unsignedBigInteger('recorded_by')->nullable()->after('sequence');
        });
    }

    public function down()
    {
        Schema::table('opd_diagnoses', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'icd10_description', 'is_primary', 'is_chronic', 'is_confirmed', 'recorded_by']);
        });

        Schema::table('opd_diagnoses', function (Blueprint $table) {
            $table->renameColumn('diagnosis_name', 'description');
        });
    }
}
