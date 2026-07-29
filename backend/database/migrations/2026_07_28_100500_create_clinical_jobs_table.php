<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Clinical job / task queue — on-call DD2 job queue + nurse N5 task timeline. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('job_type', 40)->nullable();        // review | prescribe | bloods | cannula | ...
            $table->string('priority', 20)->default('routine'); // routine | urgent | critical

            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('ward_id')->nullable()->index();
            $table->unsignedBigInteger('bed_id')->nullable();

            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('role_type', 20)->default('doctor'); // doctor | nurse
            $table->string('state', 20)->default('open');       // open | claimed | done | cancelled
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();

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
        Schema::dropIfExists('clinical_jobs');
    }
};
