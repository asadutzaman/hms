<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdbillsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_bills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_visit_id')->unique()->constrained('opd_visits')->cascadeOnDelete();

            $table->string('bill_no', 30)->unique()->index();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);

            $table->enum('status', ['unpaid','partial','paid','refunded','waived'])->default('unpaid')->index();

            $table->unsignedBigInteger('billed_by')->nullable()->index();
            $table->timestamp('billed_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'billed_at'], 'idx_opd_bills_status_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_bills');
    }
}
