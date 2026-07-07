<?php

namespace App\Models;

use App\Enums\BillRefundStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillRefund extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'bill_refunds';

    protected $fillable = [
        'organogram_id',
        'refund_no',
        'billable_type',
        'billable_id',
        'amount',
        'reason',
        'payment_method_reversed',
        'refund_status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'notes',
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
        'billable_id'   => 'integer',
        'amount'        => 'decimal:2',
        'requested_by'  => 'integer',
        'requested_at'  => 'datetime:Y-m-d H:i:s',
        'approved_by'   => 'integer',
        'approved_at'   => 'datetime:Y-m-d H:i:s',
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
        'status'        => 1,
        'sort_order'    => 0,
        'refund_status' => BillRefundStatusEnum::PENDING_APPROVAL,
    ];

    public function opdBill(): BelongsTo
    {
        return $this->belongsTo(OpdBill::class, 'billable_id');
    }

    public function ipdBill(): BelongsTo
    {
        return $this->belongsTo(IpdBill::class, 'billable_id');
    }
}
