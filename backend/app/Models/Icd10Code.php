<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;

class Icd10Code extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'organogram_id',
        'code',
        'description',
        'category',
        'is_billable',
        'status',
        'sort_order',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'          => 'integer',
        'created_by'  => 'integer',
        'updated_by'  => 'integer',
        'status'      => 'integer',
        'is_billable' => 'boolean',
        'sort_order'  => 'integer',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'      => StatusEnum::ACTIVE,
        'is_billable' => true,
    ];
}
