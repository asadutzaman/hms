<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-05-06 Machine Interfacing (ASTM/HL7) — this project has no real
 * analyzer/serial-port/TCP listener to parse actual ASTM E1394 or HL7 v2
 * wire protocol against. This table is the audit trail for whatever
 * inbound payload arrives at AnalyzerInterfaceController::import() (a
 * structured, already-parsed JSON representation of analyzer output —
 * parsing real ASTM/HL7 frames is out of scope, same "stub the unrealistic
 * hardware boundary" pattern as Sprint 9's biometric attendance sync).
 * raw_message keeps whatever was received for later audit/reference even
 * if matching to a lab_order_item fails.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_analyzer_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('analyzer_name')->nullable();
            $table->string('barcode')->nullable()->index();
            $table->longText('raw_message');
            $table->string('parse_status', 16)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('matched_result_count')->default(0);
            $table->dateTime('received_at');
            $table->dateTime('processed_at')->nullable();

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
        Schema::dropIfExists('lab_analyzer_messages');
    }
};
