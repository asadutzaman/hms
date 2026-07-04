<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceOpdVisitIdToAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('source_opd_visit_id')->nullable()->after('rescheduled_from_id')
                ->constrained('opd_visits')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_opd_visit_id');
        });
    }
}
