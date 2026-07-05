<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdPrescriptionItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_prescription_items';

    protected $fillable = [
        'organogram_id',
        'opd_prescription_id',
        'opd_visit_id',
        'drug_id',
        'drug_name',
        'generic_name',
        'strength',
        'dosage_form',
        'dose_value',
        'dispensed_quantity',
        'dose_unit',
        'frequency',
        'duration_value',
        'duration_unit',
        'route',
        'instruction',
        'is_prn',
        'sequence',
        'unit_price',
        'amount',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                  => 'integer',
        'organogram_id'       => 'integer',
        'opd_prescription_id' => 'integer',
        'opd_visit_id'        => 'integer',
        'drug_id'             => 'integer',
        'is_prn'              => 'boolean',
        'sequence'            => 'integer',
        'dose_value'          => 'decimal:3',
        'dispensed_quantity'  => 'decimal:3',
        'duration_value'      => 'integer',
        'unit_price'          => 'decimal:2',
        'amount'              => 'decimal:2',
        'sort_order'          => 'integer',
        'status'              => 'integer',
        'created_at'          => 'datetime:Y-m-d H:i:s',
        'updated_at'          => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'is_prn'     => false,
        'sort_order' => 0,
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(OpdPrescription::class, 'opd_prescription_id');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }
}
