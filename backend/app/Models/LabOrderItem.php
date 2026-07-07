<?php

namespace App\Models;

use App\Enums\LabOrderItemStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabOrderItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'lab_order_items';

    protected $fillable = [
        'organogram_id',
        'lab_order_id',
        'lab_test_id',
        'test_name_snapshot',
        'sample_type_snapshot',
        'price_snapshot',
        'item_status',
        'sequence',
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
        'lab_order_id'   => 'integer',
        'lab_test_id'    => 'integer',
        'price_snapshot' => 'decimal:2',
        'sequence'       => 'integer',
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
        'status'      => 1,
        'sort_order'  => 0,
        'sequence'    => 1,
        'item_status' => LabOrderItemStatusEnum::ORDERED,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class, 'lab_order_item_id');
    }
}
