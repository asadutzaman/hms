<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'patient_id',
        'referring_doctor_id',
        'referred_to_department_id',
        'referred_to_doctor_id',
        'external_facility_name',
        'reason',
        'urgency',
        'referral_status',
        'notes',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                          => 'integer',
        'organogram_id'               => 'integer',
        'opd_visit_id'                => 'integer',
        'patient_id'                  => 'integer',
        'referring_doctor_id'         => 'integer',
        'referred_to_department_id'   => 'integer',
        'referred_to_doctor_id'       => 'integer',
        'sort_order'                  => 'integer',
        'status'                      => 'integer',
        'created_at'                  => 'datetime:Y-m-d H:i:s',
        'updated_at'                  => 'datetime:Y-m-d H:i:s',
        'urgency'                     => 'string',
        'referral_status'             => 'string',
    ];

    protected $attributes = [
        'status'          => StatusEnum::ACTIVE,
        'urgency'         => 'routine',
        'referral_status' => 'pending',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function referringDoctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'referring_doctor_id');
    }

    public function referredToDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'referred_to_department_id');
    }

    public function referredToDoctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'referred_to_doctor_id');
    }
}
