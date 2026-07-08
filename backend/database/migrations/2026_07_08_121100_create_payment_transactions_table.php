<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-17-05 Online Payment via Portal / F-02-09 Online Payment During
 * Booking. This project has no real payment gateway account/API keys to
 * integrate against — PaymentGatewayService talks to a deterministic mock
 * provider (same "stub the unrealistic external boundary" pattern as
 * Sprint 7's SMS, Sprint 9's biometric sync, Sprint 10's analyzer
 * interface). payable_type/payable_id is a plain string+id pair (not a
 * real polymorphic relation), matching the existing
 * insurance_claims.billable_type/billable_id convention in this codebase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('transaction_ref', 64)->unique();
            $table->string('payable_type', 30)->index(); // opd_bill, ipd_bill, appointment
            $table->unsignedBigInteger('payable_id')->index();
            $table->unsignedBigInteger('patient_id')->nullable()->index();

            $table->decimal('amount', 14, 2);
            $table->string('currency', 8)->default('BDT');
            $table->string('gateway', 32)->default('mock_gateway');
            $table->string('gateway_reference', 100)->nullable();
            $table->string('payment_status', 16)->default('initiated')->index();
            $table->text('failure_reason')->nullable();

            $table->dateTime('initiated_at');
            $table->dateTime('completed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
