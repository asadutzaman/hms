<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // lab_tests was generated without the standard 'status' column that BaseModel
        // expects via $attributes['status'] = StatusEnum::ACTIVE. Add it idempotently
        // so LabTest model saves don't fail trying to write a non-existent column.
        if (! Schema::hasColumn('lab_tests', 'status')) {
            Schema::table('lab_tests', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('status_flag');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lab_tests', 'status')) {
            Schema::table('lab_tests', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};