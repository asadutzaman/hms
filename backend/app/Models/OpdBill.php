<?php

namespace App\Models;

use App\Enums\OpdBillStatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpdBill extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_bills';

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'billing_package_id',
        'bill_no',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'balance',
        'status',
        'billed_by',
        'billed_at',
        'sort_order',
        'discount_reason',
        'discount_type',
        'discount_status',
        'discount_approved_by',
        'discount_approved_at',
        'pending_discount',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                    => 'integer',
        'organogram_id'         => 'integer',
        'opd_visit_id'          => 'integer',
        'billing_package_id'    => 'integer',
        'subtotal'              => 'decimal:2',
        'discount'              => 'decimal:2',
        'tax'                   => 'decimal:2',
        'total'                 => 'decimal:2',
        'paid'                  => 'decimal:2',
        'balance'               => 'decimal:2',
        'billed_by'             => 'integer',
        'sort_order'            => 'integer',
        'status'                => 'string',
        'billed_at'             => 'datetime:Y-m-d H:i:s',
        'discount_approved_by'  => 'integer',
        'discount_approved_at'  => 'datetime:Y-m-d H:i:s',
        'pending_discount'      => 'decimal:2',
        'created_at'            => 'datetime:Y-m-d H:i:s',
        'updated_at'            => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => OpdBillStatusEnum::UNPAID,
        'sort_order' => 0,
        'subtotal'   => 0,
        'discount'   => 0,
        'tax'        => 0,
        'total'      => 0,
        'paid'       => 0,
        'balance'    => 0,
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpdBillItem::class, 'opd_bill_id')->orderBy('sequence');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OpdBillPayment::class, 'opd_bill_id')->orderBy('paid_at');
    }

    public function biller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'billed_by');
    }

    public function billingPackage(): BelongsTo
    {
        return $this->belongsTo(BillingPackage::class, 'billing_package_id');
    }
}
