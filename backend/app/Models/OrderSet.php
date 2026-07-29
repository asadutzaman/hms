<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderSet extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'order_sets';

    protected $fillable = [
        'organogram_id', 'name', 'category', 'description', 'items',
        'is_global', 'owner_user_id',
        'created_by', 'updated_by', 'sort_order', 'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'            => 'integer',
        'items'         => 'array',
        'is_global'     => 'boolean',
        'owner_user_id' => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = ['is_global' => true, 'status' => 1, 'sort_order' => 0];
}
