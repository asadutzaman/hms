<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-20-05 Settlement & Reconciliation — records the bank receipt an
 * insurer's payment was matched against, and (if the settled amount is
 * less than the claimed/approved amount) the shortfall billed back to the
 * patient via a new 'insurance_adjustment' bill-item line on the original
 * OPD/IPD bill (see InsuranceClaimSettlementService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_claim_settlements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('settlement_no', 64)->unique();
            $table->unsignedBigInteger('insurance_claim_id')->index();

            $table->string('bank_reference_no');
            $table->date('bank_receipt_date');
            $table->decimal('settled_amount', 14, 2);
            $table->decimal('shortfall_amount', 14, 2)->default(0);
            $table->boolean('patient_billed')->default(false);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('settled_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('insurance_claim_id', 'claim_settlements_claim_fk')->references('id')->on('insurance_claims')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claim_settlements');
    }
};
