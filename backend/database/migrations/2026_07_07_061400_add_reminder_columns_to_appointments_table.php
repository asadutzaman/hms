<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Idempotency guards for the scheduled reminder command — a reminder
            // is sent at most once per window per appointment.
            $table->timestamp('reminder_24h_sent_at')->nullable();
            $table->timestamp('reminder_2h_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['reminder_24h_sent_at', 'reminder_2h_sent_at']);
        });
    }
};
