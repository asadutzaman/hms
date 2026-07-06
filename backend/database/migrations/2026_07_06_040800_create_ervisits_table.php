<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('er_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('er_visit_no', 30)->unique();
            $table->unsignedBigInteger('patient_id')->index();

            $table->string('arrival_mode', 20)->default('walk_in'); // walk_in, ambulance, referred, police, other
            $table->text('chief_complaint');
            $table->dateTime('arrival_at');

            $table->string('er_status', 20)->default('waiting_triage')->index();
            $table->string('disposition', 20)->nullable(); // admitted, discharged, transferred, lwbs, deceased
            $table->unsignedBigInteger('linked_admission_id')->nullable();
            $table->dateTime('disposed_at')->nullable();

            $table->unsignedBigInteger('registered_by')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('linked_admission_id', 'er_visits_linked_admission_id_fk')->references('id')->on('ipd_admissions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('er_visits');
    }
};
