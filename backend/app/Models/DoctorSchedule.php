<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorSchedule extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'doctor_schedules';

    protected $fillable = [
        'doctor_id',
        'department_id',
        'chamber_id',
        'name',
        'schedule_type',
        'consultation_mode',
        'effective_from',
        'effective_to',
        'slot_duration_minutes',
        'max_patients_per_slot',
        'buffer_minutes',
        'consultation_fee',
        'follow_up_fee',
        'currency',
        'is_recurring',
        'allow_online_booking',
        'allow_walk_in',
        'is_default',
        'notes',
        'created_by',
        'updated_by',
        'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                     => 'integer',
        'doctor_id'              => 'integer',
        'department_id'          => 'integer',
        'chamber_id'             => 'integer',
        'slot_duration_minutes'  => 'integer',
        'max_patients_per_slot'  => 'integer',
        'buffer_minutes'         => 'integer',
        'consultation_fee'       => 'decimal:2',
        'follow_up_fee'          => 'decimal:2',
        'is_recurring'           => 'boolean',
        'allow_online_booking'   => 'boolean',
        'allow_walk_in'          => 'boolean',
        'is_default'             => 'boolean',
        'effective_from'         => 'date:Y-m-d',
        'effective_to'           => 'date:Y-m-d',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
        'status'                 => 'integer',
        'created_at'             => 'datetime:Y-m-d H:i:s',
        'updated_at'             => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'effective_from', 'effective_to', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $attributes = [
        'status'                 => StatusEnum::ACTIVE,
        'slot_duration_minutes'  => 15,
        'max_patients_per_slot'  => 1,
        'buffer_minutes'         => 0,
        'consultation_fee'       => 0,
        'follow_up_fee'          => 0,
        'currency'               => 'BDT',
        'is_recurring'           => true,
        'allow_online_booking'   => true,
        'allow_walk_in'          => true,
        'schedule_type'          => 'regular',
        'consultation_mode'      => 'in_person',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'chamber_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(DoctorScheduleSlot::class, 'doctor_schedule_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(DoctorScheduleException::class, 'doctor_schedule_id');
    }
}
