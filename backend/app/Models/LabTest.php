<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTest extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'organogram_id',
        'code',
        'name',
        'category',
        'sample_type',
        'tat_hours',
        'default_price',
        'is_active',
        'description',
        'sort_order',
        'status_flag',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'tat_hours'     => 'integer',
        'default_price' => 'decimal:2',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
        'status_flag'   => 'integer',
        'status'        => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    protected $attributes = [
        'is_active'   => true,
        'status_flag' => 1,
        'status'      => StatusEnum::ACTIVE,
    ];
}
