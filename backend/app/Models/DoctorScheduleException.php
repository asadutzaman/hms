<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorScheduleException extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'doctor_schedule_exceptions';

    protected $fillable = [
        'doctor_id',
        'doctor_schedule_id',
        'exception_date',
        'exception_type',
        'start_time',
        'end_time',
        'reason',
        'notes',
        'approval_status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                 => 'integer',
        'doctor_id'          => 'integer',
        'doctor_schedule_id' => 'integer',
        'exception_date'     => 'date:Y-m-d',
        'approved_by'        => 'integer',
        'approved_at'        => 'datetime:Y-m-d H:i:s',
        'created_by'         => 'integer',
        'updated_by'         => 'integer',
        'status'             => 'integer',
        'created_at'         => 'datetime:Y-m-d H:i:s',
        'updated_at'         => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'exception_date', 'approved_at', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $attributes = [
        'status'          => StatusEnum::ACTIVE,
        'exception_type'  => 'leave',
        'approval_status' => 'approved',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
