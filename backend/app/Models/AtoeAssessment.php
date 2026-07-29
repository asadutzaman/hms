<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtoeAssessment extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'atoe_assessments';

    protected $fillable = [
        'organogram_id', 'patient_id', 'ipd_admission_id', 'assessed_by', 'assessed_at',
        'airway', 'breathing', 'circulation', 'disability', 'exposure',
        'news2_score', 'impression', 'plan',
        'created_by', 'updated_by', 'sort_order', 'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'               => 'integer',
        'patient_id'       => 'integer',
        'ipd_admission_id' => 'integer',
        'assessed_by'      => 'integer',
        'assessed_at'      => 'datetime:Y-m-d H:i:s',
        'news2_score'      => 'integer',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = ['status' => 1, 'sort_order' => 0];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
