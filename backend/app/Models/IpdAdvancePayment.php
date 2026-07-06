<?php

namespace App\Models;

use App\Enums\IpdAdvancePaymentStatusEnum;
use App\Enums\IpdPaymentMethodEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdAdvancePayment extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_advance_payments';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'amount',
        'applied_amount',
        'payment_method',
        'reference_no',
        'notes',
        'advance_status',
        'received_by',
        'received_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'             => 'integer',
        'organogram_id'  => 'integer',
        'admission_id'   => 'integer',
        'amount'         => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'received_by'    => 'integer',
        'received_at'    => 'datetime:Y-m-d H:i:s',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'sort_order'     => 'integer',
        'status'         => 'integer',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'         => 1,
        'sort_order'     => 0,
        'applied_amount' => 0,
        'payment_method' => IpdPaymentMethodEnum::CASH,
        'advance_status' => IpdAdvancePaymentStatusEnum::RECEIVED,
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'admission_id');
    }
}
