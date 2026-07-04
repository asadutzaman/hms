<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorQuote extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'purchase_order_id',
        'requisition_id',
        'supplier_id',
        'item_id',
        'quoted_unit_price',
        'quoted_delivery_days',
        'is_selected',
        'notes',
        'submitted_at',
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
        'requisition_id'     => 'integer',
        'supplier_id'        => 'integer',
        'item_id'            => 'integer',
        'quoted_delivery_days' => 'integer',
        'is_selected'        => 'boolean',

        // Decimal
        'quoted_unit_price'  => 'decimal:2',

        //Date Time
        'submitted_at'       => 'datetime:Y-m-d H:i:s',
        'created_at'         => 'datetime:Y-m-d H:i:s',
        'updated_at'         => 'datetime:Y-m-d H:i:s',
        // String
        'notes'              => 'string',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $attributes = [
        'status'      => StatusEnum::ACTIVE,
        'is_selected' => false,
    ];

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id')->select(['id', 'supplier_name']);
    }

    public function itemInfo(): HasOne
    {
        return $this->hasOne(Item::class, 'id', 'item_id')->select(['id', 'name_en', 'name_bn', 'code']);
    }
}
