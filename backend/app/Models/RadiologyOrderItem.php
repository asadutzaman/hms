<?php

namespace App\Models;

use App\Enums\RadOrderItemStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RadiologyOrderItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'radiology_order_items';

    protected $fillable = [
        'organogram_id',
        'radiology_order_id',
        'radiology_test_id',
        'test_name_snapshot',
        'modality_snapshot',
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
        'id'                  => 'integer',
        'organogram_id'       => 'integer',
        'radiology_order_id'  => 'integer',
        'radiology_test_id'   => 'integer',
        'price_snapshot'      => 'decimal:2',
        'sequence'            => 'integer',
        'created_by'          => 'integer',
        'updated_by'          => 'integer',
        'sort_order'          => 'integer',
        'status'              => 'integer',
        'created_at'          => 'datetime:Y-m-d H:i:s',
        'updated_at'          => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'      => 1,
        'sort_order'  => 0,
        'item_status' => RadOrderItemStatusEnum::ORDERED,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RadiologyOrder::class, 'radiology_order_id');
    }

    public function radiologyTest(): BelongsTo
    {
        return $this->belongsTo(RadiologyTest::class, 'radiology_test_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(RadiologyReport::class, 'radiology_order_item_id');
    }
}
