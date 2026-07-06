<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_medication_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('admission_id')->constrained('ipd_admissions')->cascadeOnDelete();

            // Drug snapshot (mirrors OpdPrescriptionItem's drug_id + text-snapshot pattern).
            $table->unsignedBigInteger('drug_id')->nullable();
            $table->string('drug_name');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('dosage_form')->nullable();

            $table->decimal('dose_value', 8, 2)->nullable();
            $table->string('dose_unit', 20)->nullable();
            $table->string('route', 20)->default('oral');
            $table->string('frequency', 10); // OD, BD, TID, QID, HS, STAT, SOS, PRN
            $table->integer('duration_value')->nullable();
            $table->string('duration_unit', 10)->nullable(); // days, weeks
            $table->boolean('is_prn')->default(false);
            $table->text('instruction')->nullable();

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->string('order_status', 20)->default('active')->index(); // active, discontinued, completed
            $table->unsignedBigInteger('ordered_by')->nullable();
            $table->timestamp('ordered_at')->useCurrent();
            $table->unsignedBigInteger('discontinued_by')->nullable();
            $table->timestamp('discontinued_at')->nullable();
            $table->string('discontinue_reason', 255)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['admission_id', 'order_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_medication_orders');
    }
};
