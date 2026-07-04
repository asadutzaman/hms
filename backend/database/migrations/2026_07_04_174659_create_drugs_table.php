<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrugsTable extends Migration
{
    public function up()
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('item_id')->unique()->constrained('items')->cascadeOnDelete();

            $table->string('generic_name');
            $table->string('brand_name')->nullable();
            $table->string('strength')->nullable();
            $table->enum('dosage_form', ['tablet', 'capsule', 'syrup', 'injection', 'ointment', 'drops', 'inhaler', 'other'])->default('tablet');
            $table->string('hsn_code')->nullable();
            $table->boolean('is_controlled')->default(false);
            $table->string('controlled_schedule')->nullable();

            // Null = this row is itself a generic/reference drug.
            // Set = this row is a branded product mapped to that generic.
            $table->foreignId('generic_drug_id')->nullable()->constrained('drugs')->nullOnDelete();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->index('generic_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('drugs');
    }
}
