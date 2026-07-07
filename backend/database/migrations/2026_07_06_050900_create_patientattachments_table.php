<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('patient_id')->index();
            $table->string('file_id', 191)->index(); // FK to files.file_id (string PK, not a real FK constraint)

            $table->string('category', 30)->default('other')->index(); // lab_report, prescription, scan, photo, id_document, insurance, other
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // Optional link to the clinical record this attachment belongs to
            // (e.g. a specific lab order or admission) — nullable, informational only.
            $table->string('attachable_type', 100)->nullable();
            $table->unsignedBigInteger('attachable_id')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_attachments');
    }
};
