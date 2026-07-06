<?php

namespace App\Models;

use App\Enums\IpdBillStatusEnum;
use App\Enums\IpdDiscountStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpdBill extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_bills';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'bill_no',
        'subtotal',
        'discount',
        'discount_reason',
        'discount_type',
        'discount_status',
        'discount_approved_by',
        'discount_approved_at',
        'pending_discount',
        'tax',
        'total',
        'paid',
        'balance',
        'bill_status',
        'is_finalized',
        'billed_by',
        'billed_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                   => 'integer',
        'organogram_id'        => 'integer',
        'admission_id'         => 'integer',
        'subtotal'             => 'decimal:2',
        'discount'             => 'decimal:2',
        'discount_approved_by' => 'integer',
        'discount_approved_at' => 'datetime:Y-m-d H:i:s',
        'pending_discount'     => 'decimal:2',
        'tax'                  => 'decimal:2',
        'total'                => 'decimal:2',
        'paid'                 => 'decimal:2',
        'balance'              => 'decimal:2',
        'is_finalized'         => 'boolean',
        'billed_by'            => 'integer',
        'billed_at'            => 'datetime:Y-m-d H:i:s',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'sort_order'           => 'integer',
        'status'               => 'integer',
        'created_at'           => 'datetime:Y-m-d H:i:s',
        'updated_at'           => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'          => 1,
        'sort_order'      => 0,
        'subtotal'        => 0,
        'discount'        => 0,
        'discount_type'   => 'flat',
        'discount_status' => IpdDiscountStatusEnum::NONE,
        'pending_discount' => 0,
        'tax'             => 0,
        'total'           => 0,
        'paid'            => 0,
        'balance'         => 0,
        'bill_status'     => IpdBillStatusEnum::UNPAID,
        'is_finalized'    => false,
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'admission_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IpdBillItem::class, 'ipd_bill_id')->orderBy('sequence');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(IpdBillPayment::class, 'ipd_bill_id')->orderBy('paid_at');
    }
}
