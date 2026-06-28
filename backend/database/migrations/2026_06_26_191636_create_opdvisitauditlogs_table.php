<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdvisitauditlogsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_visit_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_visit_id')->constrained('opd_visits')->cascadeOnDelete();

            $table->enum('action', ['create','status_change','update','cancel','close','bill_generated','payment_recorded'])->default('update');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();

            $table->unsignedBigInteger('actor_id')->nullable()->index();

            $table->json('payload')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->timestamps();

            $table->index(['opd_visit_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_visit_audit_logs');
    }
}
