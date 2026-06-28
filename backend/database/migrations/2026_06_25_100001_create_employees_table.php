<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // Foreign keys
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('organogram_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('designation_id')->nullable()->index();

            // Identity
            $table->string('employee_id', 50)->unique()->index();
            $table->string('name_en', 150);
            $table->string('name_bn', 150)->nullable();
            $table->string('father_name', 150)->nullable();
            $table->string('mother_name', 150)->nullable();

            // Personal Info
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('religion', ['islam', 'hinduism', 'christianity', 'buddhism', 'other'])->nullable();
            $table->string('mobile', 20)->nullable();
            $table->date('dob')->nullable();
            $table->date('joining_date')->nullable();

            // Employment Info
            $table->string('employee_type', 100)->nullable();
            $table->string('employee_category', 100)->nullable();
            $table->string('employee_class', 100)->nullable();

            // Media
            $table->string('image', 255)->nullable();
            $table->string('e_signature', 255)->nullable();

            // Standard columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['name_en', 'status']);
            $table->index(['status', 'deleted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
