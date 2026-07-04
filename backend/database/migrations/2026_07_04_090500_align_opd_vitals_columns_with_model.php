<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OpdVital's model/validator/controller were written against a richer column
 * set (bp_systolic, pulse_bpm, temperature_c, spo2_pct, weight_kg, height_cm,
 * plus respiratory_rate/blood_glucose_mg_dl/pain_score/temperature_method)
 * that never made it into the original migration — every vitals save was
 * failing with "column does not exist". Rename the existing columns to match
 * and add the missing ones.
 */
class AlignOpdVitalsColumnsWithModel extends Migration
{
    public function up()
    {
        Schema::table('opd_vitals', function (Blueprint $table) {
            $table->renameColumn('systolic', 'bp_systolic');
            $table->renameColumn('diastolic', 'bp_diastolic');
            $table->renameColumn('pulse', 'pulse_bpm');
            $table->renameColumn('temperature', 'temperature_c');
            $table->renameColumn('spo2', 'spo2_pct');
            $table->renameColumn('weight', 'weight_kg');
            $table->renameColumn('height', 'height_cm');
        });

        Schema::table('opd_vitals', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('opd_visit_id');
            $table->string('temperature_method', 30)->nullable()->after('temperature_c');
            $table->unsignedSmallInteger('respiratory_rate')->nullable()->after('spo2_pct');
            $table->decimal('blood_glucose_mg_dl', 6, 2)->nullable()->after('bmi');
            $table->unsignedTinyInteger('pain_score')->nullable()->after('blood_glucose_mg_dl');
        });
    }

    public function down()
    {
        Schema::table('opd_vitals', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'temperature_method', 'respiratory_rate', 'blood_glucose_mg_dl', 'pain_score']);
        });

        Schema::table('opd_vitals', function (Blueprint $table) {
            $table->renameColumn('bp_systolic', 'systolic');
            $table->renameColumn('bp_diastolic', 'diastolic');
            $table->renameColumn('pulse_bpm', 'pulse');
            $table->renameColumn('temperature_c', 'temperature');
            $table->renameColumn('spo2_pct', 'spo2');
            $table->renameColumn('weight_kg', 'weight');
            $table->renameColumn('height_cm', 'height');
        });
    }
}
