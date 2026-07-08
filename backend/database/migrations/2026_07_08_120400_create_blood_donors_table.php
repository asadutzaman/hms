<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-11-01 Donor Management. Donors are a standalone registry, not tied to
 * the Patient table — most walk-in blood donors are never registered as
 * hospital patients.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_donors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('donor_no', 64)->unique();
            $table->string('name');
            $table->string('gender', 16)->nullable();
            $table->date('dob')->nullable();
            $table->string('blood_group', 4);
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('last_donation_date')->nullable();
            $table->unsignedInteger('total_donations')->default(0);
            $table->boolean('is_deferred')->default(false);
            $table->text('deferral_reason')->nullable();
            $table->date('deferral_until_date')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_donors');
    }
};
