<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class RateContract extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'supplier_id',
        'item_id',
        'vendor_quote_id',
        'contract_price',
        'valid_from',
        'valid_to',
        'contract_status',
        'process_status',
        'approved_by',
        'approved_at',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        // Integer
        'id'               => 'integer',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'status'           => 'integer',
        'supplier_id'      => 'integer',
        'item_id'          => 'integer',
        'vendor_quote_id'  => 'integer',
        'approved_by'      => 'integer',

        // Decimal
        'contract_price'   => 'decimal:2',

        //Date
        'valid_from'       => 'date:Y-m-d',
        'valid_to'         => 'date:Y-m-d',
        //Date Time
        'approved_at'      => 'datetime:Y-m-d H:i:s',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
        // String
        'contract_status'  => 'string',
        'process_status'   => 'string',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'          => StatusEnum::ACTIVE,
        'contract_status' => 'pending_approval',
        'process_status'  => 'DRAFT',
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
