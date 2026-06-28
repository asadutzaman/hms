<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentAuditLog extends BaseModel
{
    public static $uuIdPrefix = '';

    use Autofill, Uuid;

    protected $table = 'appointment_audit_logs';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'action',
        'from_status',
        'to_status',
        'payload',
        'remarks',
        'actor_type',
        'actor_id',
        'ip_address',
        'user_agent',
        'occurred_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'id'             => 'integer',
        'appointment_id' => 'integer',
        'patient_id'     => 'integer',
        'doctor_id'      => 'integer',
        'actor_id'       => 'integer',
        'payload'        => 'array',
        'occurred_at'    => 'datetime:Y-m-d H:i:s',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'status'         => 'integer',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'actor_type' => 'user',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
