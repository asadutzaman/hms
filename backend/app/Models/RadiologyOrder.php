<?php

namespace App\Models;

use App\Enums\RadOrderStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyOrder extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'radiology_orders';

    protected $fillable = [
        'organogram_id',
        'rad_order_no',
        'patient_id',
        'opd_visit_id',
        'ipd_admission_id',
        'order_source',
        'ordered_by',
        'ordered_at',
        'priority',
        'clinical_indication',
        'order_status',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'               => 'integer',
        'organogram_id'    => 'integer',
        'patient_id'       => 'integer',
        'opd_visit_id'     => 'integer',
        'ipd_admission_id' => 'integer',
        'ordered_by'       => 'integer',
        'ordered_at'       => 'datetime:Y-m-d H:i:s',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'       => 1,
        'sort_order'   => 0,
        'order_source' => 'walk_in',
        'priority'     => 'routine',
        'order_status' => RadOrderStatusEnum::ORDERED,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function ipdAdmission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RadiologyOrderItem::class, 'radiology_order_id')->orderBy('sequence');
    }
}
