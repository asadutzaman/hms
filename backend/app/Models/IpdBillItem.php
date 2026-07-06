<?php

namespace App\Models;

use App\Enums\IpdBillItemTypeEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdBillItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_bill_items';

    protected $fillable = [
        'organogram_id',
        'ipd_bill_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'source_type',
        'source_id',
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
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'ipd_bill_id'   => 'integer',
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'line_total'    => 'decimal:2',
        'source_id'     => 'integer',
        'sequence'      => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'     => 1,
        'sort_order' => 0,
        'quantity'   => 1,
        'item_type'  => IpdBillItemTypeEnum::OTHER,
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(IpdBill::class, 'ipd_bill_id');
    }
}
