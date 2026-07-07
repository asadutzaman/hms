<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-17-01 Patient Login & Profile — OTP codes for the patient portal's
 * email-based login. Deliberately NOT reusing anything from the staff
 * oauth_access_tokens/users stack (see project_hms_sprint8_scope memory) —
 * this is the first-ever patient-facing auth flow in the app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_otp_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->string('email')->index();
            $table->string('code_hash');
            $table->string('purpose', 20)->default('login');

            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->unsignedTinyInteger('attempt_count')->default(0);

            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_otp_codes');
    }
};
