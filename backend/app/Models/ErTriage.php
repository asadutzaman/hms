<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErTriage extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'er_triages';

    protected $fillable = [
        'organogram_id',
        'er_visit_id',
        'triage_level',
        'color_band',
        'target_minutes',
        'bp_systolic',
        'bp_diastolic',
        'pulse_bpm',
        'temperature_c',
        'spo2_pct',
        'respiratory_rate',
        'notes',
        'triaged_by',
        'triaged_at',
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
        'er_visit_id'      => 'integer',
        'triage_level'     => 'integer',
        'target_minutes'   => 'integer',
        'bp_systolic'      => 'integer',
        'bp_diastolic'     => 'integer',
        'pulse_bpm'        => 'integer',
        'temperature_c'    => 'decimal:1',
        'spo2_pct'         => 'integer',
        'respiratory_rate' => 'integer',
        'triaged_by'       => 'integer',
        'triaged_at'       => 'datetime:Y-m-d H:i:s',
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
        'status'     => 1,
        'sort_order' => 0,
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(ErVisit::class, 'er_visit_id');
    }
}
