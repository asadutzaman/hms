<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-09-03 Anaesthesia Record — one header row per ot_booking (1:1), with
 * the "every 5 min" chart entries in the child anaesthesia_record_entries
 * table (mirrors the ipd_vitals pattern of a periodic-reading child table).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anaesthesia_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('ot_booking_id')->unique();
            $table->unsignedBigInteger('anaesthetist_id')->nullable();

            $table->string('anaesthesia_type', 32)->default('general');
            $table->string('asa_grade', 16)->nullable();
            $table->text('premedication')->nullable();
            $table->string('induction_agent')->nullable();
            $table->string('airway_management')->nullable();
            $table->text('notes')->nullable();
            $table->text('recovery_notes')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('ot_booking_id')->references('id')->on('ot_bookings')->onDelete('cascade');
            $table->foreign('anaesthetist_id', 'anaesthesia_records_anaesthetist_fk')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anaesthesia_records');
    }
};
