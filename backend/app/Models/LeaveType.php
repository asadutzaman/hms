<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'leave_types';

    protected $fillable = [
        'organogram_id',
        'name',
        'max_days_per_year',
        'is_paid',
        'description',
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
        'max_days_per_year' => 'integer',
        'is_paid'           => 'boolean',
        'created_by'        => 'integer',
        'updated_by'        => 'integer',
        'sort_order'        => 'integer',
        'status'            => 'integer',
        'created_at'        => 'datetime:Y-m-d H:i:s',
        'updated_at'        => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'is_paid'    => true,
        'sort_order' => 0,
    ];
}
