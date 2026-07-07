<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('claim_no', 30)->unique();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('insurance_company_id')->index();
            $table->unsignedBigInteger('insurance_scheme_id')->nullable()->index();
            $table->unsignedBigInteger('pre_authorization_id')->nullable()->index();
            $table->string('policy_number')->nullable();

            // Polymorphic — the claim can be raised against an OPD or IPD bill.
            $table->string('billable_type', 20); // opd_bill, ipd_bill
            $table->unsignedBigInteger('billable_id');

            $table->decimal('claimed_amount', 14, 2);
            $table->decimal('approved_amount', 14, 2)->nullable();

            $table->string('claim_status', 20)->default('draft')->index();
            // draft -> submitted -> under_review -> approved|partially_approved|rejected -> settled

            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['billable_type', 'billable_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('insurance_company_id', 'insurance_claims_company_id_fk')->references('id')->on('insurance_companies')->onDelete('restrict');
            $table->foreign('insurance_scheme_id', 'insurance_claims_scheme_id_fk')->references('id')->on('insurance_schemes')->onDelete('set null');
            $table->foreign('pre_authorization_id', 'insurance_claims_pre_auth_id_fk')->references('id')->on('pre_authorizations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
    }
};
