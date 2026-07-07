<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('rad_order_no', 30)->unique();
            $table->unsignedBigInteger('patient_id')->index();

            $table->unsignedBigInteger('opd_visit_id')->nullable()->index();
            $table->unsignedBigInteger('ipd_admission_id')->nullable()->index();
            $table->string('order_source', 10)->default('walk_in')->index(); // opd, ipd, walk_in

            $table->unsignedBigInteger('ordered_by')->nullable();
            $table->timestamp('ordered_at')->useCurrent();
            $table->string('priority', 10)->default('routine')->index(); // routine, urgent, stat
            $table->text('clinical_indication')->nullable();

            $table->string('order_status', 20)->default('ordered')->index();
            // ordered -> in_progress -> reported ; cancelled terminal from any non-terminal state

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('opd_visit_id', 'rad_orders_opd_visit_id_fk')->references('id')->on('opd_visits')->onDelete('set null');
            $table->foreign('ipd_admission_id', 'rad_orders_ipd_admission_id_fk')->references('id')->on('ipd_admissions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_orders');
    }
};
