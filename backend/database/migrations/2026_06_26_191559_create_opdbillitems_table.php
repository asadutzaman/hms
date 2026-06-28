<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdbillitemsTable extends Migration
{
    public function up()
    {
        Schema::create('opd_bill_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('opd_bill_id')->constrained('opd_bills')->cascadeOnDelete();

            $table->enum('item_type', ['consultation','prescription','investigation','other'])->default('other');
            $table->string('description', 255);
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            // Source traceability (polymorphic-light)
            $table->string('source_type', 50)->nullable();     // e.g. "App\Models\OpdPrescriptionItem"
            $table->unsignedBigInteger('source_id')->nullable();

            $table->unsignedSmallInteger('sequence')->default(1);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status_flag')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['opd_bill_id', 'sequence']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_bill_items');
    }
}
