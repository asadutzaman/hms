<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('filename', 255);
            $table->string('disk_path', 500);
            $table->bigInteger('size_bytes')->nullable();
            $table->string('backup_status', 20)->default('running')->index(); // running, success, failed
            $table->text('failure_reason')->nullable();
            $table->string('triggered_by_type', 20)->default('manual'); // manual, scheduled
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
