<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalledAtToOpdVisitsTable extends Migration
{
    public function up()
    {
        Schema::table('opd_visits', function (Blueprint $table) {
            $table->timestamp('called_at')->nullable()->after('token_number');
        });
    }

    public function down()
    {
        Schema::table('opd_visits', function (Blueprint $table) {
            $table->dropColumn('called_at');
        });
    }
}
