<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_bills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('admission_id')->unique()->constrained('ipd_admissions')->cascadeOnDelete();

            $table->string('bill_no', 30)->unique()->index();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('discount_reason', 255)->nullable();
            $table->string('discount_type', 10)->default('flat');
            $table->string('discount_status', 20)->default('none');
            $table->unsignedBigInteger('discount_approved_by')->nullable();
            $table->timestamp('discount_approved_at')->nullable();
            $table->decimal('pending_discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('bill_status', 20)->default('unpaid')->index();
            $table->boolean('is_finalized')->default(false)->index();

            $table->unsignedBigInteger('billed_by')->nullable()->index();
            $table->timestamp('billed_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['bill_status', 'billed_at'], 'idx_ipd_bills_status_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_bills');
    }
};
