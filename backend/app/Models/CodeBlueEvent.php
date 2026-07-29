<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeBlueEvent extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'code_blue_events';

    protected $fillable = [
        'organogram_id',
        'event_type',
        'patient_id',
        'ward_id',
        'bed_id',
        'location',
        'state',
        'severity',
        'reason',
        'responders',
        'outcome_notes',
        'raised_by',
        'raised_at',
        'responded_at',
        'resolved_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'            => 'integer',
        'patient_id'    => 'integer',
        'ward_id'       => 'integer',
        'bed_id'        => 'integer',
        'responders'    => 'array',
        'raised_by'     => 'integer',
        'raised_at'     => 'datetime:Y-m-d H:i:s',
        'responded_at'  => 'datetime:Y-m-d H:i:s',
        'resolved_at'   => 'datetime:Y-m-d H:i:s',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'event_type' => 'code_blue',
        'state'      => 'active',
        'status'     => 1,
        'sort_order' => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
}
