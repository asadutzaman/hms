<?php

namespace App\Models;

use App\Enums\OpdPaymentMethodEnum;
use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdBillPayment extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_bill_payments';

    protected $fillable = [
        'organogram_id',
        'opd_bill_id',
        'patient_id',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_no',
        'bank_name',
        'cheque_no',
        'cheque_date',
        'received_by',
        'notes',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'opd_bill_id'   => 'integer',
        'patient_id'    => 'integer',
        'amount'        => 'decimal:2',
        'received_by'   => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'payment_date'  => 'datetime:Y-m-d H:i:s',
        'cheque_date'   => 'date:Y-m-d',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'         => StatusEnum::ACTIVE,
        'payment_method' => OpdPaymentMethodEnum::CASH,
        'sort_order'     => 0,
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(OpdBill::class, 'opd_bill_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
