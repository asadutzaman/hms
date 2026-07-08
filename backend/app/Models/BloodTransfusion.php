<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodTransfusion extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'blood_transfusions';

    protected $fillable = [
        'organogram_id',
        'patient_id',
        'blood_unit_id',
        'cross_match_id',
        'ipd_admission_id',
        'started_at',
        'ended_at',
        'reaction_observed',
        'reaction_notes',
        'administered_by',
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
        'patient_id'         => 'integer',
        'blood_unit_id'      => 'integer',
        'cross_match_id'     => 'integer',
        'ipd_admission_id'   => 'integer',
        'started_at'         => 'datetime:Y-m-d H:i:s',
        'ended_at'           => 'datetime:Y-m-d H:i:s',
        'reaction_observed'  => 'boolean',
        'administered_by'    => 'integer',
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
        'status'             => StatusEnum::ACTIVE,
        'reaction_observed'  => false,
        'sort_order'         => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class, 'blood_unit_id');
    }

    public function crossMatch(): BelongsTo
    {
        return $this->belongsTo(BloodCrossMatch::class, 'cross_match_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }
}
