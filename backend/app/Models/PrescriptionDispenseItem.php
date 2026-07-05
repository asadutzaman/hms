<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionDispenseItem extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_prescription_dispense_items';

    protected $fillable = [
        'opd_prescription_dispense_id',
        'opd_prescription_item_id',
        'drug_id',
        'dispensed_quantity',
        'expire_date',
        'remarks',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        // Integer
        'id'                            => 'integer',
        'created_by'                    => 'integer',
        'updated_by'                    => 'integer',
        'status'                        => 'integer',
        'opd_prescription_dispense_id'  => 'integer',
        'opd_prescription_item_id'      => 'integer',
        'drug_id'                       => 'integer',
        // Decimal
        'dispensed_quantity'            => 'decimal:3',
        //Date
        'expire_date'                   => 'date:Y-m-d',
        //Date Time
        'created_at'                    => 'datetime:Y-m-d H:i:s',
        'updated_at'                    => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status' => StatusEnum::ACTIVE,
    ];

    public function dispense(): BelongsTo
    {
        return $this->belongsTo(PrescriptionDispense::class, 'opd_prescription_dispense_id');
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(OpdPrescriptionItem::class, 'opd_prescription_item_id');
    }

    public function drug(): HasOne
    {
        return $this->hasOne(Drug::class, 'id', 'drug_id')->with('item');
    }
}
