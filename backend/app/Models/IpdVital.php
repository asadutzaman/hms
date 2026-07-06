<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdVital extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_vitals';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'recorded_at',
        'recorded_by',
        'bp_systolic',
        'bp_diastolic',
        'pulse_bpm',
        'temperature_c',
        'temperature_method',
        'spo2_pct',
        'respiratory_rate',
        'weight_kg',
        'height_cm',
        'bmi',
        'blood_glucose_mg_dl',
        'pain_score',
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
        'id'                  => 'integer',
        'organogram_id'       => 'integer',
        'admission_id'        => 'integer',
        'recorded_at'         => 'datetime:Y-m-d H:i:s',
        'recorded_by'         => 'integer',
        'bp_systolic'         => 'integer',
        'bp_diastolic'        => 'integer',
        'pulse_bpm'           => 'integer',
        'temperature_c'       => 'decimal:1',
        'spo2_pct'            => 'integer',
        'respiratory_rate'    => 'integer',
        'weight_kg'           => 'decimal:2',
        'height_cm'           => 'decimal:2',
        'bmi'                 => 'decimal:2',
        'blood_glucose_mg_dl' => 'decimal:2',
        'pain_score'          => 'integer',
        'created_by'          => 'integer',
        'updated_by'          => 'integer',
        'sort_order'          => 'integer',
        'status'              => 'integer',
        'created_at'          => 'datetime:Y-m-d H:i:s',
        'updated_at'          => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'     => 1,
        'sort_order' => 0,
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'admission_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
