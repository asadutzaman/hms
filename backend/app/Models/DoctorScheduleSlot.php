<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorScheduleSlot extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'doctor_schedule_slots';

    protected $fillable = [
        'doctor_schedule_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'max_patients_per_slot',
        'session_label',
        'is_active',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [];

    protected $casts = [
        'id'                       => 'integer',
        'doctor_schedule_id'       => 'integer',
        'day_of_week'              => 'integer',
        'slot_duration_minutes'    => 'integer',
        'max_patients_per_slot'    => 'integer',
        'is_active'                => 'boolean',
        'created_by'               => 'integer',
        'updated_by'               => 'integer',
        'status'                   => 'integer',
        'created_at'               => 'datetime:Y-m-d H:i:s',
        'updated_at'               => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'    => StatusEnum::ACTIVE,
        'is_active' => true,
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id');
    }
}
