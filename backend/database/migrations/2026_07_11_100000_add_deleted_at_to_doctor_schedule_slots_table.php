<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The DoctorScheduleSlot model uses SoftDeletes (project standard, matching
// its sibling doctor_schedules / doctor_schedule_exceptions tables) but the
// create migration only added timestamps(), so every query on the model hit
// "column deleted_at does not exist". Add the missing column.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('doctor_schedule_slots', 'deleted_at')) {
            Schema::table('doctor_schedule_slots', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('doctor_schedule_slots', 'deleted_at')) {
            Schema::table('doctor_schedule_slots', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
