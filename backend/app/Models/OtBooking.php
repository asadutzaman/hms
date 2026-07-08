<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OtBooking extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ot_bookings';

    protected $fillable = [
        'organogram_id',
        'booking_no',
        'patient_id',
        'ipd_admission_id',
        'theatre_id',
        'department_id',
        'surgeon_id',
        'anaesthetist_id',
        'surgery_name',
        'surgery_type',
        'scheduled_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'equipment_list',
        'booking_status',
        'cancellation_reason',
        'notes',
        'booked_by',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                => 'integer',
        'organogram_id'     => 'integer',
        'patient_id'        => 'integer',
        'ipd_admission_id'  => 'integer',
        'theatre_id'        => 'integer',
        'department_id'     => 'integer',
        'surgeon_id'        => 'integer',
        'anaesthetist_id'   => 'integer',
        'booked_by'         => 'integer',
        'created_by'        => 'integer',
        'updated_by'        => 'integer',
        'sort_order'        => 'integer',
        'status'            => 'integer',
        'equipment_list'    => 'array',
        'scheduled_date'    => 'date:Y-m-d',
        'actual_start_time' => 'datetime:Y-m-d H:i:s',
        'actual_end_time'   => 'datetime:Y-m-d H:i:s',
        'created_at'        => 'datetime:Y-m-d H:i:s',
        'updated_at'        => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'         => StatusEnum::ACTIVE,
        'surgery_type'   => 'elective',
        'booking_status' => 'scheduled',
        'sort_order'     => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }

    public function theatre(): BelongsTo
    {
        return $this->belongsTo(Theatre::class, 'theatre_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'surgeon_id');
    }

    public function anaesthetist(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'anaesthetist_id');
    }

    public function surgeryNote(): HasOne
    {
        return $this->hasOne(SurgeryNote::class, 'ot_booking_id');
    }

    public function anaesthesiaRecord(): HasOne
    {
        return $this->hasOne(AnaesthesiaRecord::class, 'ot_booking_id');
    }
}
