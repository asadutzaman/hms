<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('ipd_bill_id')->constrained('ipd_bills')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20)->default('cash');
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('paid_by')->nullable()->index();
            $table->timestamp('paid_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index(['ipd_bill_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_bill_payments');
    }
};
