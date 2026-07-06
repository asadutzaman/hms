<?php

namespace App\Models;

use App\Enums\IpdMedicationOrderStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpdMedicationOrder extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_medication_orders';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'drug_id',
        'drug_name',
        'generic_name',
        'strength',
        'dosage_form',
        'dose_value',
        'dose_unit',
        'route',
        'frequency',
        'duration_value',
        'duration_unit',
        'is_prn',
        'instruction',
        'start_date',
        'end_date',
        'order_status',
        'ordered_by',
        'ordered_at',
        'discontinued_by',
        'discontinued_at',
        'discontinue_reason',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'              => 'integer',
        'organogram_id'   => 'integer',
        'admission_id'    => 'integer',
        'drug_id'         => 'integer',
        'dose_value'      => 'decimal:2',
        'duration_value'  => 'integer',
        'is_prn'          => 'boolean',
        'start_date'      => 'date:Y-m-d',
        'end_date'        => 'date:Y-m-d',
        'ordered_by'      => 'integer',
        'ordered_at'      => 'datetime:Y-m-d H:i:s',
        'discontinued_by' => 'integer',
        'discontinued_at' => 'datetime:Y-m-d H:i:s',
        'created_by'      => 'integer',
        'updated_by'      => 'integer',
        'sort_order'      => 'integer',
        'status'          => 'integer',
        'created_at'      => 'datetime:Y-m-d H:i:s',
        'updated_at'      => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'       => 1,
        'sort_order'   => 0,
        'route'        => 'oral',
        'is_prn'       => false,
        'order_status' => IpdMedicationOrderStatusEnum::ACTIVE,
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'admission_id');
    }

    public function administrations(): HasMany
    {
        return $this->hasMany(IpdMedicationAdministration::class, 'order_id')->orderBy('scheduled_at');
    }
}
