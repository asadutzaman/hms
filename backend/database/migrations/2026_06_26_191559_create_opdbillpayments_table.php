<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdbillpaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_bill_id')->constrained('opd_bills')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash','card','insurance','mobile','bank','other'])->default('cash');
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('paid_by')->nullable()->index();
            $table->timestamp('paid_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['opd_bill_id', 'paid_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_bill_payments');
    }
}
