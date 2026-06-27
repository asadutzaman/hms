<?php

namespace App\Models;

use App\Enums\OpdBillStatusEnum;
use App\Enums\StatusEnum;
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
        'patient_id',
        'bill_no',
        'bill_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'due_amount',
        'discount_reason',
        'is_cancelled',
        'cancelled_reason',
        'cancelled_at',
        'generated_by',
        'generated_at',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'               => 'integer',
        'organogram_id'    => 'integer',
        'opd_visit_id'     => 'integer',
        'patient_id'       => 'integer',
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'grand_total'      => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'due_amount'       => 'decimal:2',
        'is_cancelled'     => 'boolean',
        'generated_by'     => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'string',
        'bill_date'        => 'date:Y-m-d',
        'generated_at'     => 'datetime:Y-m-d H:i:s',
        'cancelled_at'     => 'datetime:Y-m-d H:i:s',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'          => OpdBillStatusEnum::UNPAID,
        'is_cancelled'    => false,
        'sort_order'      => 0,
        'subtotal'        => 0,
        'discount_amount' => 0,
        'tax_amount'      => 0,
        'grand_total'     => 0,
        'paid_amount'     => 0,
        'due_amount'      => 0,
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpdBillItem::class, 'opd_bill_id')->orderBy('sequence');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OpdBillPayment::class, 'opd_bill_id')->orderBy('payment_date');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
