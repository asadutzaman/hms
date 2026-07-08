<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BloodUnit extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'blood_units';

    protected $fillable = [
        'organogram_id',
        'bag_no',
        'donation_id',
        'component_type',
        'blood_group',
        'collection_date',
        'expiry_date',
        'screening_status',
        'screening_results',
        'unit_status',
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
        'id'                 => 'integer',
        'organogram_id'      => 'integer',
        'donation_id'        => 'integer',
        'collection_date'    => 'date:Y-m-d',
        'expiry_date'        => 'date:Y-m-d',
        'screening_results'  => 'array',
        'created_by'         => 'integer',
        'updated_by'         => 'integer',
        'sort_order'         => 'integer',
        'status'             => 'integer',
        'created_at'         => 'datetime:Y-m-d H:i:s',
        'updated_at'         => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'            => StatusEnum::ACTIVE,
        'component_type'    => 'whole_blood',
        'screening_status'  => 'pending',
        'unit_status'       => 'quarantine',
        'sort_order'        => 0,
    ];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(BloodDonation::class, 'donation_id');
    }

    public function transfusion(): HasOne
    {
        return $this->hasOne(BloodTransfusion::class, 'blood_unit_id');
    }
}
