<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organogram_id')->nullable();

            $table->unsignedBigInteger('radiology_order_item_id')->unique();
            $table->unsignedBigInteger('radiology_report_template_id')->nullable()->index();

            $table->text('findings')->nullable();
            $table->text('impression')->nullable();

            $table->string('report_status', 20)->default('draft')->index();
            // draft -> final ; amended after a final report is edited again

            $table->unsignedBigInteger('reported_by')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->foreign('radiology_order_item_id', 'rad_reports_order_item_id_fk')->references('id')->on('radiology_order_items')->onDelete('cascade');
            $table->foreign('radiology_report_template_id', 'rad_reports_template_id_fk')->references('id')->on('radiology_report_templates')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_reports');
    }
};
