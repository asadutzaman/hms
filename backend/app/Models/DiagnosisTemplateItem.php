<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosisTemplateItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'diagnosis_template_id',
        'diagnosis_type',
        'icd10_id',
        'icd10_code',
        'icd10_description',
        'diagnosis_name',
        'notes',
        'sequence',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                     => 'integer',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
        'status'                 => 'integer',
        'diagnosis_template_id'  => 'integer',
        'icd10_id'               => 'integer',
        'sequence'               => 'integer',
        'created_at'             => 'datetime:Y-m-d H:i:s',
        'updated_at'             => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'         => StatusEnum::ACTIVE,
        'diagnosis_type' => 'primary',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DiagnosisTemplate::class, 'diagnosis_template_id');
    }

    public function icd10(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_id');
    }
}
