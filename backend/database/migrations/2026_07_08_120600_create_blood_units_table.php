<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-11-02 Blood Inventory & Screening. One donation can be separated into
 * several component units (whole blood is rarely transfused as-is), so
 * donation_id is nullable-but-usually-set (also allows externally supplied
 * units with no local donation record). unit_status starts at 'quarantine'
 * until screening_status flips to 'passed' — an unscreened unit must never
 * be issuable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('bag_no', 64)->unique();
            $table->unsignedBigInteger('donation_id')->nullable()->index();
            $table->string('component_type', 24)->default('whole_blood')->index();
            $table->string('blood_group', 4)->index();
            $table->date('collection_date');
            $table->date('expiry_date')->index();

            $table->string('screening_status', 16)->default('pending')->index();
            $table->json('screening_results')->nullable();
            $table->string('unit_status', 16)->default('quarantine')->index();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('donation_id', 'blood_units_donation_fk')->references('id')->on('blood_donations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_units');
    }
};
