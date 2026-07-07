<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('lab_test_id')->nullable();

            // Snapshot at order time (catalog may change later).
            $table->string('test_name_snapshot');
            $table->string('sample_type_snapshot', 50)->nullable();
            $table->decimal('price_snapshot', 12, 2)->default(0);

            $table->string('item_status', 20)->default('ordered')->index();
            $table->unsignedSmallInteger('sequence')->default(1);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('lab_test_id', 'lab_order_items_lab_test_id_fk')->references('id')->on('lab_tests')->onDelete('set null');
            $table->index(['lab_order_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_items');
    }
};
