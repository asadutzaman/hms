<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdDiagnosis extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_diagnoses';

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'patient_id',
        'diagnosis_type',
        'icd10_id',
        'icd10_code',
        'icd10_description',
        'diagnosis_name',
        'is_primary',
        'is_chronic',
        'is_confirmed',
        'notes',
        'sequence',
        'recorded_by',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'              => 'integer',
        'organogram_id'   => 'integer',
        'opd_visit_id'    => 'integer',
        'patient_id'      => 'integer',
        'icd10_id'        => 'integer',
        'sequence'        => 'integer',
        'recorded_by'     => 'integer',
        'is_primary'      => 'boolean',
        'is_chronic'      => 'boolean',
        'is_confirmed'    => 'boolean',
        'sort_order'      => 'integer',
        'status'          => 'integer',
        'created_at'      => 'datetime:Y-m-d H:i:s',
        'updated_at'      => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'       => StatusEnum::ACTIVE,
        'is_primary'   => false,
        'is_chronic'   => false,
        'is_confirmed' => false,
        'sort_order'   => 0,
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

    public function icd10(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_id');
    }
}
