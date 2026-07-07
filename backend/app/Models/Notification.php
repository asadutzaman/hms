<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'notifications';

    protected $fillable = [
        'organogram_id',
        'user_id',
        'channel',
        'type',
        'title',
        'body',
        'data',
        'delivery_status',
        'failure_reason',
        'sent_at',
        'is_read',
        'read_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'              => 'integer',
        'organogram_id'   => 'integer',
        'user_id'         => 'integer',
        'data'            => 'array',
        'sent_at'         => 'datetime:Y-m-d H:i:s',
        'is_read'         => 'boolean',
        'read_at'         => 'datetime:Y-m-d H:i:s',
        'created_by'      => 'integer',
        'updated_by'      => 'integer',
        'sort_order'      => 'integer',
        'status'          => 'integer',
        'created_at'      => 'datetime:Y-m-d H:i:s',
        'updated_at'      => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'          => 1,
        'sort_order'      => 0,
        'is_read'         => false,
        'delivery_status' => 'pending',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
