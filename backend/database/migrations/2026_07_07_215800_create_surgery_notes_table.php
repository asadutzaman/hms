<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * F-09-02 Surgery Notes & WHO Safety Checklist — one row per ot_booking
 * (1:1). The WHO Surgical Safety Checklist's three phases (Sign In / Time
 * Out / Sign Out) are each stored as a JSON checklist-items blob plus a
 * completed-by/completed-at signature pair, rather than a separate
 * checklist-items table — the checklist item set is fixed (WHO's standard
 * form), not user-configurable, so a normalized items table would be
 * unnecessary structure for this sprint's scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgery_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('ot_booking_id')->unique();

            $table->text('pre_op_notes')->nullable();

            $table->json('who_sign_in_checklist')->nullable();
            $table->unsignedBigInteger('who_sign_in_by')->nullable();
            $table->dateTime('who_sign_in_at')->nullable();

            $table->json('who_time_out_checklist')->nullable();
            $table->unsignedBigInteger('who_time_out_by')->nullable();
            $table->dateTime('who_time_out_at')->nullable();

            $table->json('who_sign_out_checklist')->nullable();
            $table->unsignedBigInteger('who_sign_out_by')->nullable();
            $table->dateTime('who_sign_out_at')->nullable();

            $table->text('procedure_performed')->nullable();
            $table->text('intra_op_notes')->nullable();
            $table->text('post_op_notes')->nullable();
            $table->text('complications')->nullable();

            $table->unsignedBigInteger('surgeon_signed_by')->nullable();
            $table->dateTime('surgeon_signed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('ot_booking_id')->references('id')->on('ot_bookings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_notes');
    }
};
