<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdDischargeSummary extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_discharge_summaries';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'summary_no',
        'admission_diagnosis',
        'discharge_diagnosis',
        'hospital_course',
        'procedures_performed',
        'discharge_condition',
        'discharge_medications',
        'follow_up_instructions',
        'discharge_advice',
        'is_finalized',
        'signed_by',
        'signed_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                     => 'integer',
        'organogram_id'          => 'integer',
        'admission_id'           => 'integer',
        'discharge_medications'  => 'array',
        'is_finalized'           => 'boolean',
        'signed_by'              => 'integer',
        'signed_at'              => 'datetime:Y-m-d H:i:s',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
        'sort_order'             => 'integer',
        'status'                 => 'integer',
        'created_at'             => 'datetime:Y-m-d H:i:s',
        'updated_at'             => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'       => 1,
        'sort_order'   => 0,
        'is_finalized' => false,
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'admission_id');
    }
}
