<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentWaitlist extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'appointment_waitlists';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'department_id',
        'preferred_slot_id',
        'converted_appointment_id',
        'preferred_date_from',
        'preferred_date_to',
        'time_preference',
        'priority',
        'queue_position',
        'status',
        'notified_at',
        'notification_expires_at',
        'notification_attempts',
        'reason_for_visit',
        'notes',
        'created_by',
        'updated_by',
        'sort_order',
        'status_active',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                       => 'integer',
        'patient_id'               => 'integer',
        'doctor_id'                => 'integer',
        'department_id'            => 'integer',
        'preferred_slot_id'        => 'integer',
        'converted_appointment_id' => 'integer',
        'priority'                 => 'integer',
        'queue_position'           => 'integer',
        'notification_attempts'    => 'integer',
        'preferred_date_from'      => 'date:Y-m-d',
        'preferred_date_to'        => 'date:Y-m-d',
        'notified_at'              => 'datetime:Y-m-d H:i:s',
        'notification_expires_at'  => 'datetime:Y-m-d H:i:s',
        'created_by'               => 'integer',
        'updated_by'               => 'integer',
        'status_active'            => 'integer',
        'created_at'               => 'datetime:Y-m-d H:i:s',
        'updated_at'               => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status_active'         => 1,
        'status'                => 'waiting',
        'priority'              => 5,
        'queue_position'        => 0,
        'time_preference'       => 'any',
        'notification_attempts' => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function preferredSlot(): BelongsTo
    {
        return $this->belongsTo(AppointmentSlot::class, 'preferred_slot_id');
    }

    public function convertedAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'converted_appointment_id');
    }
}
