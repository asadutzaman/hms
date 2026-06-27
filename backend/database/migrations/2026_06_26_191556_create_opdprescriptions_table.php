<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdprescriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_visit_id')->unique()->constrained('opd_visits')->cascadeOnDelete();

            $table->unsignedBigInteger('prescribed_by')->nullable()->index();
            $table->timestamp('prescribed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->text('advice')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_prescriptions');
    }
}
