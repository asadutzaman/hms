<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdinvestigationordersTable extends Migration
{
    public function up()
    {
        Schema::create('opd_investigation_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_visit_id')->constrained('opd_visits')->cascadeOnDelete();

            $table->unsignedBigInteger('ordered_by')->nullable()->index();
            $table->timestamp('ordered_at')->useCurrent();
            $table->enum('status', ['ordered','collected','processing','completed','cancelled'])->default('ordered');
            $table->text('notes')->nullable();
            $table->text('clinical_indication')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['opd_visit_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_investigation_orders');
    }
}
