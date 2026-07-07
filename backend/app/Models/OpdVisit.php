<?php

namespace App\Models;

use App\Enums\OpdVisitStatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OpdVisit extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_visits';

    protected $fillable = [
        'organogram_id',
        'opd_no',
        'patient_id',
        'appointment_id',
        'doctor_id',
        'department_id',
        'visit_type',
        'visit_date',
        'token_number',
        'called_at',
        'status',
        'chief_complaint',
        'history',
        'examination',
        'clinical_notes',
        'advice',
        'consultation_start_at',
        'consultation_end_at',
        'closed_at',
        'cancellation_reason',
        'cancelled_at',
        'closed_by',
        'created_by',
        'updated_by',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                    => 'integer',
        'organogram_id'         => 'integer',
        'patient_id'            => 'integer',
        'appointment_id'        => 'integer',
        'doctor_id'             => 'integer',
        'department_id'         => 'integer',
        'token_number'          => 'integer',
        'sort_order'            => 'integer',
        'status'                => 'string',
        'visit_date'            => 'date:Y-m-d',
        'called_at'             => 'datetime:Y-m-d H:i:s',
        'consultation_start_at' => 'datetime:Y-m-d H:i:s',
        'consultation_end_at'   => 'datetime:Y-m-d H:i:s',
        'closed_at'             => 'datetime:Y-m-d H:i:s',
        'cancelled_at'          => 'datetime:Y-m-d H:i:s',
        'closed_by'             => 'integer',
        'created_by'            => 'integer',
        'updated_by'            => 'integer',
        'created_at'            => 'datetime:Y-m-d H:i:s',
        'updated_at'            => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => OpdVisitStatusEnum::WAITING,
        'visit_type' => 'walk_in',
        'sort_order' => 0,
    ];

    /* ---------- Relationships ---------- */

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function vitals(): HasOne
    {
        return $this->hasOne(OpdVital::class, 'opd_visit_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(OpdDiagnosis::class, 'opd_visit_id')->orderBy('sequence');
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(OpdPrescription::class, 'opd_visit_id');
    }

    public function investigationOrders(): HasMany
    {
        return $this->hasMany(OpdInvestigationOrder::class, 'opd_visit_id')->orderBy('ordered_at');
    }

    /**
     * Sprint 6 LIS orders — deliberately separate from investigationOrders()
     * above, which is dead code (its Model/Validator/Resource reference DB
     * columns that were never migrated; see project_hms_sprint6_scope memory).
     */
    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class, 'opd_visit_id')->orderBy('ordered_at');
    }

    public function radiologyOrders(): HasMany
    {
        return $this->hasMany(RadiologyOrder::class, 'opd_visit_id')->orderBy('ordered_at');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(OpdBill::class, 'opd_visit_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(OpdVisitAuditLog::class, 'opd_visit_id')->orderByDesc('created_at');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'opd_visit_id')->orderByDesc('id');
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(OpdProcedure::class, 'opd_visit_id')->orderByDesc('performed_at');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
