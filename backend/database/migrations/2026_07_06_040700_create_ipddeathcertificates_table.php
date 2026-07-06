<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_death_certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->foreignId('admission_id')->unique()->constrained('ipd_admissions')->cascadeOnDelete();

            $table->string('certificate_no', 30)->unique();

            $table->date('date_of_death');
            $table->time('time_of_death')->nullable();

            $table->text('immediate_cause')->nullable();
            $table->text('antecedent_cause')->nullable();
            $table->text('underlying_cause')->nullable();
            $table->text('other_significant_conditions')->nullable();
            $table->string('manner_of_death', 20)->default('natural'); // natural, accident, suicide, homicide, undetermined

            $table->boolean('is_finalized')->default(false);
            $table->unsignedBigInteger('certified_by')->nullable();
            $table->timestamp('certified_at')->nullable();

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
        Schema::dropIfExists('ipd_death_certificates');
    }
};
