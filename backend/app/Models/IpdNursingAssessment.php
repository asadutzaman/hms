<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdNursingAssessment extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_nursing_assessments';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'general_appearance',
        'mobility_status',
        'fall_risk_score',
        'fall_risk_level',
        'pressure_injury_risk_score',
        'pressure_injury_risk_level',
        'pain_assessment',
        'nutrition_risk',
        'skin_integrity_notes',
        'psychosocial_notes',
        'care_plan_notes',
        'assessed_by',
        'assessed_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                         => 'integer',
        'organogram_id'              => 'integer',
        'admission_id'               => 'integer',
        'fall_risk_score'            => 'integer',
        'pressure_injury_risk_score' => 'integer',
        'assessed_by'                => 'integer',
        'assessed_at'                => 'datetime:Y-m-d H:i:s',
        'created_by'                 => 'integer',
        'updated_by'                 => 'integer',
        'sort_order'                 => 'integer',
        'status'                     => 'integer',
        'created_at'                 => 'datetime:Y-m-d H:i:s',
        'updated_at'                 => 'datetime:Y-m-d H:i:s',
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
}
