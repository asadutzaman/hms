<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaimSettlement extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'insurance_claim_settlements';

    protected $fillable = [
        'organogram_id',
        'settlement_no',
        'insurance_claim_id',
        'bank_reference_no',
        'bank_receipt_date',
        'settled_amount',
        'shortfall_amount',
        'patient_billed',
        'notes',
        'settled_by',
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
        'insurance_claim_id'  => 'integer',
        'settled_amount'      => 'decimal:2',
        'shortfall_amount'    => 'decimal:2',
        'patient_billed'      => 'boolean',
        'settled_by'          => 'integer',
        'created_by'          => 'integer',
        'updated_by'          => 'integer',
        'sort_order'          => 'integer',
        'status'              => 'integer',
        'bank_receipt_date'   => 'date:Y-m-d',
        'created_at'          => 'datetime:Y-m-d H:i:s',
        'updated_at'          => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'          => StatusEnum::ACTIVE,
        'shortfall_amount' => 0,
        'patient_billed'  => false,
        'sort_order'      => 0,
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(InsuranceClaim::class, 'insurance_claim_id');
    }
}
