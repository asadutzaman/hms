<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabAnalyzerMessage extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'lab_analyzer_messages';

    protected $fillable = [
        'organogram_id',
        'analyzer_name',
        'barcode',
        'raw_message',
        'parse_status',
        'error_message',
        'matched_result_count',
        'received_at',
        'processed_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                    => 'integer',
        'organogram_id'         => 'integer',
        'matched_result_count'  => 'integer',
        'created_by'            => 'integer',
        'updated_by'            => 'integer',
        'sort_order'            => 'integer',
        'status'                => 'integer',
        'received_at'           => 'datetime:Y-m-d H:i:s',
        'processed_at'          => 'datetime:Y-m-d H:i:s',
        'created_at'            => 'datetime:Y-m-d H:i:s',
        'updated_at'            => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'       => StatusEnum::ACTIVE,
        'parse_status' => 'pending',
        'sort_order'   => 0,
    ];
}
