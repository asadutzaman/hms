<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'lab_results';

    protected $fillable = [
        'organogram_id',
        'lab_order_item_id',
        'lab_test_parameter_id',
        'parameter_name_snapshot',
        'unit_snapshot',
        'result_value',
        'result_flag',
        'reference_range_display',
        'verification_status',
        'entered_by',
        'entered_at',
        'verified_by',
        'verified_at',
        'remarks',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                     => 'integer',
        'organogram_id'          => 'integer',
        'lab_order_item_id'      => 'integer',
        'lab_test_parameter_id'  => 'integer',
        'entered_by'             => 'integer',
        'entered_at'             => 'datetime:Y-m-d H:i:s',
        'verified_by'            => 'integer',
        'verified_at'            => 'datetime:Y-m-d H:i:s',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
        'sort_order'             => 'integer',
        'status'                 => 'integer',
        'created_at'             => 'datetime:Y-m-d H:i:s',
        'updated_at'             => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'              => 1,
        'sort_order'          => 0,
        'verification_status' => 'pending',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(LabOrderItem::class, 'lab_order_item_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(LabTestParameter::class, 'lab_test_parameter_id');
    }
}
