<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionDispense extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_prescription_dispenses';

    protected $fillable = [
        'organogram_id',
        'opd_prescription_id',
        'patient_id',
        'branch_id',
        'dispensed_by',
        'dispensed_at',
        'dispense_status',
        'notes',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        // Integer
        'id'                   => 'integer',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'status'               => 'integer',
        'opd_prescription_id'  => 'integer',
        'patient_id'           => 'integer',
        'branch_id'            => 'integer',
        'dispensed_by'         => 'integer',
        //Date Time
        'dispensed_at'         => 'datetime:Y-m-d H:i:s',
        'created_at'           => 'datetime:Y-m-d H:i:s',
        'updated_at'           => 'datetime:Y-m-d H:i:s',
        // String
        'dispense_status'      => 'string',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'          => StatusEnum::ACTIVE,
        'dispense_status' => 'partial',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(OpdPrescription::class, 'opd_prescription_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function branch(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id')->select(['id', 'name']);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionDispenseItem::class, 'opd_prescription_dispense_id');
    }
}
