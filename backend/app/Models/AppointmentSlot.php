<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentSlot extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'appointment_slots';

    protected $fillable = [
        'doctor_id',
        'doctor_schedule_id',
        'department_id',
        'chamber_id',
        'slot_date',
        'start_time',
        'end_time',
        'slot_start_at',
        'slot_end_at',
        'max_patients',
        'booked_count',
        'hold_count',
        'walk_in_count',
        'waitlist_count',
        'is_blocked',
        'block_reason',
        'is_extra_slot',
        'status',
        'created_by',
        'updated_by',
        'sort_order',
        'status_active',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                   => 'integer',
        'doctor_id'            => 'integer',
        'doctor_schedule_id'   => 'integer',
        'department_id'        => 'integer',
        'chamber_id'           => 'integer',
        'slot_date'            => 'date:Y-m-d',
        'slot_start_at'        => 'datetime:Y-m-d H:i:s',
        'slot_end_at'          => 'datetime:Y-m-d H:i:s',
        'max_patients'         => 'integer',
        'booked_count'         => 'integer',
        'hold_count'           => 'integer',
        'walk_in_count'        => 'integer',
        'waitlist_count'       => 'integer',
        'is_blocked'           => 'boolean',
        'is_extra_slot'        => 'boolean',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'sort_order'           => 'integer',
        'status_active'        => 'integer',
        'created_at'           => 'datetime:Y-m-d H:i:s',
        'updated_at'           => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status_active' => 1,
        'status'        => 'open',
        'max_patients'  => 1,
        'booked_count'  => 0,
        'hold_count'    => 0,
        'walk_in_count' => 0,
        'waitlist_count'=> 0,
        'is_blocked'    => false,
        'is_extra_slot' => false,
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'chamber_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'appointment_slot_id');
    }

    public function getRemainingCapacityAttribute(): int
    {
        return max(0, (int) $this->max_patients - (int) $this->booked_count - (int) $this->walk_in_count);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->remaining_capacity <= 0;
    }
}
