<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_package_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('billing_package_id')->index();
            $table->string('item_type', 20)->default('other'); // mirrors OpdBillItem/IpdBillItem item_type values
            $table->string('description');
            $table->unsignedInteger('default_quantity')->default(1);
            $table->decimal('notional_unit_price', 14, 2)->nullable(); // display-only; package itself is fixed-price
            $table->unsignedSmallInteger('sequence')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('billing_package_id')->references('id')->on('billing_packages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_package_items');
    }
};
