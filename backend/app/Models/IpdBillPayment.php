<?php

namespace App\Models;

use App\Enums\IpdPaymentMethodEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdBillPayment extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_bill_payments';

    protected $fillable = [
        'organogram_id',
        'ipd_bill_id',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
        'paid_by',
        'paid_at',
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
        'amount'        => 'decimal:2',
        'paid_by'       => 'integer',
        'paid_at'       => 'datetime:Y-m-d H:i:s',
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
        'status'         => 1,
        'sort_order'     => 0,
        'payment_method' => IpdPaymentMethodEnum::CASH,
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(IpdBill::class, 'ipd_bill_id');
    }
}
