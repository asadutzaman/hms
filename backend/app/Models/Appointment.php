<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'appointments';

    protected $fillable = [
        'appointment_no',
        'patient_id',
        'doctor_id',
        'department_id',
        'chamber_id',
        'appointment_slot_id',
        'source',
        'consultation_mode',
        'appointment_date',
        'start_time',
        'end_time',
        'appointment_at',
        'token_number',
        'token_sequence',
        'status',
        'payment_status',
        'consultation_fee',
        'paid_amount',
        'currency',
        'reason_for_visit',
        'symptoms',
        'notes',
        'cancellation_reason',
        'rescheduled_from_id',
        'source_opd_visit_id',
        'reschedule_count',
        'confirmed_at',
        'checked_in_at',
        'consultation_started_at',
        'consultation_ended_at',
        'cancelled_at',
        'completed_at',
        'booked_by',
        'cancelled_by',
        'created_by',
        'updated_by',
        'sort_order',
        'status_active',
        'reminder_24h_sent_at',
        'reminder_2h_sent_at',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                       => 'integer',
        'patient_id'               => 'integer',
        'doctor_id'                => 'integer',
        'department_id'            => 'integer',
        'chamber_id'               => 'integer',
        'appointment_slot_id'      => 'integer',
        'rescheduled_from_id'      => 'integer',
        'source_opd_visit_id'      => 'integer',
        'reschedule_count'         => 'integer',
        'token_number'             => 'integer',
        'token_sequence'           => 'integer',
        'consultation_fee'         => 'decimal:2',
        'paid_amount'              => 'decimal:2',
        'appointment_date'         => 'date:Y-m-d',
        'appointment_at'           => 'datetime:Y-m-d H:i:s',
        'confirmed_at'             => 'datetime:Y-m-d H:i:s',
        'checked_in_at'            => 'datetime:Y-m-d H:i:s',
        'consultation_started_at'  => 'datetime:Y-m-d H:i:s',
        'consultation_ended_at'    => 'datetime:Y-m-d H:i:s',
        'cancelled_at'             => 'datetime:Y-m-d H:i:s',
        'completed_at'             => 'datetime:Y-m-d H:i:s',
        'booked_by'                => 'integer',
        'cancelled_by'             => 'integer',
        'created_by'               => 'integer',
        'updated_by'               => 'integer',
        'status_active'            => 'integer',
        'reminder_24h_sent_at'     => 'datetime:Y-m-d H:i:s',
        'reminder_2h_sent_at'      => 'datetime:Y-m-d H:i:s',
        'created_at'               => 'datetime:Y-m-d H:i:s',
        'updated_at'               => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status_active'    => 1,
        'status'           => 'confirmed',
        'source'           => 'online',
        'consultation_mode'=> 'in_person',
        'payment_status'   => 'unpaid',
        'currency'         => 'BDT',
        'consultation_fee' => 0,
        'paid_amount'      => 0,
        'reschedule_count' => 0,
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

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'chamber_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(AppointmentSlot::class, 'appointment_slot_id');
    }

    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'rescheduled_from_id');
    }

    public function sourceOpdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'source_opd_visit_id');
    }

    public function bookedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AppointmentAuditLog::class, 'appointment_id');
    }
}
