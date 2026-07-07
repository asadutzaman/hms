<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupLog extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'backup_logs';

    protected $fillable = [
        'organogram_id',
        'filename',
        'disk_path',
        'size_bytes',
        'backup_status',
        'failure_reason',
        'triggered_by_type',
        'triggered_by',
        'started_at',
        'completed_at',
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
        'size_bytes'     => 'integer',
        'triggered_by'   => 'integer',
        'started_at'     => 'datetime:Y-m-d H:i:s',
        'completed_at'   => 'datetime:Y-m-d H:i:s',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'sort_order'     => 'integer',
        'status'         => 'integer',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'             => 1,
        'sort_order'         => 0,
        'backup_status'      => 'running',
        'triggered_by_type'  => 'manual',
    ];
}
