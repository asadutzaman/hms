<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_authorizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('pa_no', 30)->unique();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('ipd_admission_id')->nullable()->index();
            $table->unsignedBigInteger('opd_visit_id')->nullable()->index();
            $table->unsignedBigInteger('insurance_company_id')->index();
            $table->unsignedBigInteger('insurance_scheme_id')->nullable()->index();
            $table->string('policy_number')->nullable();

            $table->decimal('estimated_amount', 14, 2);
            $table->decimal('approved_amount', 14, 2)->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();

            $table->string('pa_status', 20)->default('submitted')->index();
            // submitted -> under_review -> approved|rejected ; expired/cancelled terminal from submitted/under_review

            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->unsignedBigInteger('responded_by')->nullable();
            $table->text('response_notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('ipd_admission_id', 'pre_auths_ipd_admission_id_fk')->references('id')->on('ipd_admissions')->onDelete('set null');
            $table->foreign('opd_visit_id', 'pre_auths_opd_visit_id_fk')->references('id')->on('opd_visits')->onDelete('set null');
            $table->foreign('insurance_company_id', 'pre_auths_insurance_company_id_fk')->references('id')->on('insurance_companies')->onDelete('restrict');
            $table->foreign('insurance_scheme_id', 'pre_auths_insurance_scheme_id_fk')->references('id')->on('insurance_schemes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_authorizations');
    }
};
