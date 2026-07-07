<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_refunds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('refund_no', 30)->unique();

            // Polymorphic — a refund can be raised against an OPD or IPD bill.
            $table->string('billable_type', 20); // opd_bill, ipd_bill
            $table->unsignedBigInteger('billable_id');

            $table->decimal('amount', 14, 2);
            $table->text('reason');
            $table->string('payment_method_reversed', 20)->nullable();

            $table->string('refund_status', 20)->default('pending_approval')->index();
            // pending_approval -> approved -> processed ; rejected terminal from pending_approval

            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['billable_type', 'billable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_refunds');
    }
};
