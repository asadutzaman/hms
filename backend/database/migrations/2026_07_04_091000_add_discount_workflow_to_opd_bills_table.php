<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountWorkflowToOpdBillsTable extends Migration
{
    public function up()
    {
        Schema::table('opd_bills', function (Blueprint $table) {
            $table->string('discount_reason', 255)->nullable()->after('discount');
            $table->string('discount_type', 10)->default('flat')->after('discount_reason');
            $table->string('discount_status', 20)->default('none')->after('discount_type');
            $table->unsignedBigInteger('discount_approved_by')->nullable()->after('discount_status');
            $table->timestamp('discount_approved_at')->nullable()->after('discount_approved_by');
            // Pending-discount amount held for approval, applied to total/balance only once approved.
            $table->decimal('pending_discount', 12, 2)->default(0)->after('discount_approved_at');
        });
    }

    public function down()
    {
        Schema::table('opd_bills', function (Blueprint $table) {
            $table->dropColumn([
                'discount_reason',
                'discount_type',
                'discount_status',
                'discount_approved_by',
                'discount_approved_at',
                'pending_discount',
            ]);
        });
    }
}
