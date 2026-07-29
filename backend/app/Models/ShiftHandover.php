<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftHandover extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'shift_handovers';

    protected $fillable = [
        'organogram_id', 'role_type', 'ward_id', 'from_user_id', 'to_user_id',
        'shift_label', 'summary', 'items', 'state', 'handed_over_at',
        'created_by', 'updated_by', 'sort_order', 'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'             => 'integer',
        'ward_id'        => 'integer',
        'from_user_id'   => 'integer',
        'to_user_id'     => 'integer',
        'items'          => 'array',
        'handed_over_at' => 'datetime:Y-m-d H:i:s',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'sort_order'     => 'integer',
        'status'         => 'integer',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = ['role_type' => 'doctor', 'state' => 'draft', 'status' => 1, 'sort_order' => 0];
}
