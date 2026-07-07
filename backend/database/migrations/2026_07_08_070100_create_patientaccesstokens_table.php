<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Patient portal session tokens — a single table (unlike the staff stack's
 * separate access/refresh token tables), since the portal doesn't need
 * refresh-token rotation semantics distinct from the access token itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('patient_id')->index();
            $table->text('access_token');
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);

            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_access_tokens');
    }
};
