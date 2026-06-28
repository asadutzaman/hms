<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdBillItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_bill_items';

    protected $fillable = [
        'organogram_id',
        'opd_bill_id',
        'item_type',
        'reference_type',
        'reference_id',
        'description',
        'code',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'amount',
        'sequence',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'opd_bill_id'   => 'integer',
        'reference_id'  => 'integer',
        'quantity'      => 'decimal:3',
        'unit_price'    => 'decimal:2',
        'discount'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'amount'        => 'decimal:2',
        'sequence'      => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'sort_order' => 0,
        'quantity'   => 1,
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(OpdBill::class, 'opd_bill_id');
    }
}
