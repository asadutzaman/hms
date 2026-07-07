<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPackage extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'billing_packages';

    protected $fillable = [
        'organogram_id',
        'code',
        'name',
        'package_type',
        'fixed_price',
        'description',
        'is_active',
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
        'fixed_price'   => 'decimal:2',
        'is_active'     => 'boolean',
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
        'status'       => 1,
        'sort_order'   => 0,
        'package_type' => 'ipd',
        'is_active'    => true,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BillingPackageItem::class, 'billing_package_id')->orderBy('sequence');
    }
}
