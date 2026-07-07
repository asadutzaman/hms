<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('radiology_order_id')->index();
            $table->unsignedBigInteger('radiology_test_id')->nullable()->index();
            $table->string('test_name_snapshot');
            $table->string('modality_snapshot', 20)->nullable();
            $table->decimal('price_snapshot', 12, 2)->nullable();

            $table->string('item_status', 20)->default('ordered')->index();
            // ordered -> in_progress -> reported -> verified ; cancelled terminal
            $table->unsignedSmallInteger('sequence')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('radiology_order_id')->references('id')->on('radiology_orders')->onDelete('cascade');
            $table->foreign('radiology_test_id', 'rad_order_items_radiology_test_id_fk')->references('id')->on('radiology_tests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_order_items');
    }
};
