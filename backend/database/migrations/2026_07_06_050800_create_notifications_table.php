<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('user_id')->index(); // recipient
            $table->string('channel', 10); // in_app, email, sms
            $table->string('type', 60); // matches notification_templates.key, or a free-form event key
            $table->string('title', 255)->nullable();
            $table->text('body')->nullable();
            $table->json('data')->nullable(); // structured payload for frontend deep-linking

            $table->string('delivery_status', 20)->default('pending')->index(); // pending, sent, failed
            $table->text('failure_reason')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
