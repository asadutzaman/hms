<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPackageItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'billing_package_items';

    protected $fillable = [
        'organogram_id',
        'billing_package_id',
        'item_type',
        'description',
        'default_quantity',
        'notional_unit_price',
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
        'billing_package_id'  => 'integer',
        'default_quantity'    => 'integer',
        'notional_unit_price' => 'decimal:2',
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
        'status'           => 1,
        'sort_order'       => 0,
        'default_quantity' => 1,
        'item_type'        => 'other',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(BillingPackage::class, 'billing_package_id');
    }
}
