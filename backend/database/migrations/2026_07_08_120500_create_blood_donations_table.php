<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_donations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('donation_no', 64)->unique();
            $table->unsignedBigInteger('donor_id')->index();
            $table->date('donation_date')->index();
            $table->unsignedSmallInteger('volume_ml')->default(450);
            $table->decimal('hemoglobin_g_dl', 4, 1)->nullable();
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('donor_id', 'blood_donations_donor_fk')->references('id')->on('blood_donors')->onDelete('restrict');
            $table->foreign('collected_by', 'blood_donations_collected_by_fk')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_donations');
    }
};
