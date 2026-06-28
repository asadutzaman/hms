<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-day counter for OPD Visit No (format: OPD-YYYYMMDD-####).
 * Each row is one calendar day; next_sequence is incremented atomically
 * inside a DB::transaction with row-level lock so concurrent creates
 * cannot collide.
 */
class CreateOpdVisitSequencesTable extends Migration
{
    public function up()
    {
        Schema::create('opd_visit_sequences', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date')->unique();
            $table->unsignedInteger('next_sequence')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_visit_sequences');
    }
}