<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only regulatory audit ledger for controlled-drug dispensing — rows
 * are never updated or soft-deleted once written.
 */
class ControlledDrugRegister extends BaseModel
{
    public static $uuIdPrefix = '';

    use Uuid;

    protected $fillable = [
        'drug_id',
        'patient_id',
        'opd_prescription_item_id',
        'dispensed_quantity',
        'dispensed_by',
        'witnessed_by',
        'dispensed_at',
        'remarks',
    ];

    protected $casts = [
        'id'                        => 'integer',
        'drug_id'                   => 'integer',
        'patient_id'                => 'integer',
        'opd_prescription_item_id'  => 'integer',
        'dispensed_quantity'        => 'float',
        'dispensed_by'              => 'integer',
        'witnessed_by'              => 'integer',
        'dispensed_at'              => 'datetime:Y-m-d H:i:s',
        'created_at'                => 'datetime:Y-m-d H:i:s',
        'updated_at'                => 'datetime:Y-m-d H:i:s',
    ];

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(OpdPrescriptionItem::class, 'opd_prescription_item_id');
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witnessed_by');
    }
}
