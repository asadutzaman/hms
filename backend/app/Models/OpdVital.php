<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdVital extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_vitals';

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'patient_id',
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
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                  => 'integer',
        'organogram_id'       => 'integer',
        'opd_visit_id'        => 'integer',
        'patient_id'          => 'integer',
        'recorded_by'         => 'integer',
        'bp_systolic'         => 'integer',
        'bp_diastolic'        => 'integer',
        'pulse_bpm'           => 'integer',
        'temperature_c'       => 'decimal:2',
        'spo2_pct'            => 'integer',
        'respiratory_rate'    => 'integer',
        'weight_kg'           => 'decimal:2',
        'height_cm'           => 'decimal:2',
        'bmi'                 => 'decimal:2',
        'blood_glucose_mg_dl' => 'decimal:2',
        'pain_score'          => 'integer',
        'sort_order'          => 'integer',
        'status'              => 'integer',
        'recorded_at'         => 'datetime:Y-m-d H:i:s',
        'created_at'          => 'datetime:Y-m-d H:i:s',
        'updated_at'          => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'sort_order' => 0,
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
