<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'leave_requests';

    protected $fillable = [
        'organogram_id',
        'request_no',
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'process_status',
        'applied_by',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'             => 'integer',
        'organogram_id'  => 'integer',
        'employee_id'    => 'integer',
        'leave_type_id'  => 'integer',
        'total_days'     => 'decimal:2',
        'applied_by'     => 'integer',
        'approved_by'    => 'integer',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'sort_order'     => 'integer',
        'status'         => 'integer',
        'start_date'     => 'date:Y-m-d',
        'end_date'       => 'date:Y-m-d',
        'approved_at'    => 'datetime:Y-m-d H:i:s',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'         => StatusEnum::ACTIVE,
        'process_status' => 'DRAFT',
        'sort_order'     => 0,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
