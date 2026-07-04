<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAuditLog extends BaseModel
{
    public static $uuIdPrefix = '';

    use Autofill, Uuid;

    protected $table = 'patient_audit_logs';

    protected $fillable = [
        'patient_id',
        'merged_into_patient_id',
        'action',
        'old_values',
        'new_values',
        'remarks',
        'actor_type',
        'actor_id',
        'ip_address',
        'user_agent',
        'occurred_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'id'                      => 'integer',
        'patient_id'              => 'integer',
        'merged_into_patient_id'  => 'integer',
        'actor_id'                => 'integer',
        'old_values'              => 'array',
        'new_values'              => 'array',
        'occurred_at'             => 'datetime:Y-m-d H:i:s',
        'created_by'              => 'integer',
        'updated_by'              => 'integer',
        'status'                  => 'integer',
        'created_at'              => 'datetime:Y-m-d H:i:s',
        'updated_at'              => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'actor_type' => 'user',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function mergedIntoPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'merged_into_patient_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
