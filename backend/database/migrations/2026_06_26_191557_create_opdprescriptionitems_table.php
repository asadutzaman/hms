<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdprescriptionitemsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_prescription_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_prescription_id')->constrained('opd_prescriptions')->cascadeOnDelete();

            $table->string('drug_name', 200);
            $table->string('dose', 50)->nullable();
            $table->enum('frequency', ['OD','BD','TID','QID','HS','SOS','STAT','PRN'])->default('OD');
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->enum('route', ['oral','iv','im','sc','topical','inhalation','rectal','other'])->default('oral');
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('sequence')->default(1);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['opd_prescription_id', 'sequence']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_prescription_items');
    }
}
