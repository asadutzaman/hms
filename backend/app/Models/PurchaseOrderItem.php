<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'quantity',
        'unit_price',
        'line_total',
        'received_quantity',
        'remarks',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        // Integer
        'id'                 => 'integer',
        'created_by'         => 'integer',
        'updated_by'         => 'integer',
        'status'             => 'integer',
        'purchase_order_id'  => 'integer',
        'item_id'            => 'integer',

        // Float
        'quantity'           => 'decimal:2',
        'unit_price'         => 'decimal:2',
        'line_total'         => 'decimal:2',
        'received_quantity'  => 'decimal:2',

        //Date Time
        'created_at'         => 'datetime:Y-m-d H:i:s',
        'updated_at'         => 'datetime:Y-m-d H:i:s',
        // String
        'remarks'            => 'string',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $attributes = [
        'status'             => StatusEnum::ACTIVE,
        'received_quantity'  => 0,
    ];

    public function itemInfo(): HasOne
    {
        return $this->hasOne(Item::class, 'id', 'item_id')->select(['id', 'name_en', 'name_bn', 'code']);
    }
}
