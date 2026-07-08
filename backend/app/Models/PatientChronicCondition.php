<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientChronicCondition extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'patient_chronic_conditions';

    protected $fillable = [
        'organogram_id',
        'patient_id',
        'condition_name',
        'icd10_code_id',
        'diagnosed_date',
        'target_notes',
        'condition_status',
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
        'id'             => 'integer',
        'organogram_id'  => 'integer',
        'patient_id'     => 'integer',
        'icd10_code_id'  => 'integer',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'sort_order'     => 'integer',
        'status'         => 'integer',
        'diagnosed_date' => 'date:Y-m-d',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'           => StatusEnum::ACTIVE,
        'condition_status' => 'active',
        'sort_order'       => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function icd10Code(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_code_id');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(PatientChronicConditionReading::class, 'condition_id')->orderByDesc('reading_date');
    }
}
