<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opd_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('billing_package_id')->nullable()->index()->after('opd_visit_id');
            $table->foreign('billing_package_id', 'opd_bills_billing_package_id_fk')->references('id')->on('billing_packages')->onDelete('set null');
        });

        Schema::table('ipd_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('billing_package_id')->nullable()->index()->after('admission_id');
            $table->foreign('billing_package_id', 'ipd_bills_billing_package_id_fk')->references('id')->on('billing_packages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('opd_bills', function (Blueprint $table) {
            $table->dropForeign('opd_bills_billing_package_id_fk');
            $table->dropColumn('billing_package_id');
        });

        Schema::table('ipd_bills', function (Blueprint $table) {
            $table->dropForeign('ipd_bills_billing_package_id_fk');
            $table->dropColumn('billing_package_id');
        });
    }
};
