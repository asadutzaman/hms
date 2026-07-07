<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Deliberately extends plain Eloquent Model, not BaseModel — this is
 * auth-plumbing for the patient portal, not a staff-audited CRUD resource
 * (no organogram_id/created_by, no AuditLogTrait). See
 * project_hms_sprint8_scope memory.
 */
class PatientOtpCode extends Model
{
    public static $uuIdPrefix = '';

    use Uuid;

    protected $table = 'patient_otp_codes';

    protected $guarded = [];

    protected $casts = [
        'id'            => 'integer',
        'patient_id'    => 'integer',
        'attempt_count' => 'integer',
        'expires_at'    => 'datetime:Y-m-d H:i:s',
        'consumed_at'   => 'datetime:Y-m-d H:i:s',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'purpose'       => 'login',
        'attempt_count' => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
