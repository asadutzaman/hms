<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Enums\IpdAdmissionStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IpdAdmission extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'organogram_id',
        'admission_no',
        'patient_id',
        'opd_visit_id',
        'admission_type',
        'attending_doctor_id',
        'department_id',
        'ward_id',
        'bed_id',
        'branch_id',
        'admission_date',
        'expected_discharge_date',
        'discharge_date',
        'admission_status',
        'diagnosis_at_admission',
        'discharge_override_reason',
        'admitted_by',
        'discharged_by',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                      => 'integer',
        'organogram_id'           => 'integer',
        'patient_id'              => 'integer',
        'opd_visit_id'            => 'integer',
        'attending_doctor_id'     => 'integer',
        'department_id'           => 'integer',
        'ward_id'                 => 'integer',
        'bed_id'                  => 'integer',
        'branch_id'               => 'integer',
        'admitted_by'             => 'integer',
        'discharged_by'           => 'integer',
        'created_by'              => 'integer',
        'updated_by'              => 'integer',
        'sort_order'              => 'integer',
        'status'                  => 'integer',
        'admission_date'          => 'datetime:Y-m-d H:i:s',
        'expected_discharge_date' => 'date:Y-m-d',
        'discharge_date'          => 'datetime:Y-m-d H:i:s',
        'created_at'              => 'datetime:Y-m-d H:i:s',
        'updated_at'              => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'           => StatusEnum::ACTIVE,
        'admission_type'   => 'emergency',
        'admission_status' => IpdAdmissionStatusEnum::ADMITTED,
        'sort_order'       => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'attending_doctor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(IpdBill::class, 'admission_id');
    }

    public function advancePayments(): HasMany
    {
        return $this->hasMany(IpdAdvancePayment::class, 'admission_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(IpdAdmissionAuditLog::class, 'ipd_admission_id')->orderByDesc('created_at');
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(IpdVital::class, 'admission_id')->orderBy('recorded_at');
    }

    public function nursingAssessment(): HasOne
    {
        return $this->hasOne(IpdNursingAssessment::class, 'admission_id');
    }

    public function fluidBalances(): HasMany
    {
        return $this->hasMany(IpdFluidBalance::class, 'admission_id')->orderBy('recorded_at');
    }

    public function medicationOrders(): HasMany
    {
        return $this->hasMany(IpdMedicationOrder::class, 'admission_id')->orderByDesc('ordered_at');
    }

    public function dischargeSummary(): HasOne
    {
        return $this->hasOne(IpdDischargeSummary::class, 'admission_id');
    }

    public function deathCertificate(): HasOne
    {
        return $this->hasOne(IpdDeathCertificate::class, 'admission_id');
    }
}
