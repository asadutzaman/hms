<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalJob extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'clinical_jobs';

    protected $fillable = [
        'organogram_id', 'title', 'description', 'job_type', 'priority',
        'patient_id', 'ward_id', 'bed_id', 'requested_by', 'assigned_to',
        'role_type', 'state', 'due_at', 'completed_at',
        'created_by', 'updated_by', 'sort_order', 'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'           => 'integer',
        'patient_id'   => 'integer',
        'ward_id'      => 'integer',
        'bed_id'       => 'integer',
        'requested_by' => 'integer',
        'assigned_to'  => 'integer',
        'due_at'       => 'datetime:Y-m-d H:i:s',
        'completed_at' => 'datetime:Y-m-d H:i:s',
        'created_by'   => 'integer',
        'updated_by'   => 'integer',
        'sort_order'   => 'integer',
        'status'       => 'integer',
        'created_at'   => 'datetime:Y-m-d H:i:s',
        'updated_at'   => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = ['priority' => 'routine', 'role_type' => 'doctor', 'state' => 'open', 'status' => 1, 'sort_order' => 0];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
}
