<?php

namespace App\Models;

use App\Enums\PreAuthorizationStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreAuthorization extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'pre_authorizations';

    protected $fillable = [
        'organogram_id',
        'pa_no',
        'patient_id',
        'ipd_admission_id',
        'opd_visit_id',
        'insurance_company_id',
        'insurance_scheme_id',
        'policy_number',
        'estimated_amount',
        'approved_amount',
        'diagnosis',
        'treatment_plan',
        'pa_status',
        'requested_by',
        'requested_at',
        'responded_at',
        'responded_by',
        'response_notes',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                    => 'integer',
        'organogram_id'         => 'integer',
        'patient_id'            => 'integer',
        'ipd_admission_id'      => 'integer',
        'opd_visit_id'          => 'integer',
        'insurance_company_id'  => 'integer',
        'insurance_scheme_id'   => 'integer',
        'estimated_amount'      => 'decimal:2',
        'approved_amount'       => 'decimal:2',
        'requested_by'          => 'integer',
        'requested_at'          => 'datetime:Y-m-d H:i:s',
        'responded_at'          => 'datetime:Y-m-d H:i:s',
        'responded_by'          => 'integer',
        'created_by'            => 'integer',
        'updated_by'            => 'integer',
        'sort_order'            => 'integer',
        'status'                => 'integer',
        'created_at'            => 'datetime:Y-m-d H:i:s',
        'updated_at'            => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'    => 1,
        'sort_order' => 0,
        'pa_status' => PreAuthorizationStatusEnum::SUBMITTED,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function ipdAdmission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function insuranceScheme(): BelongsTo
    {
        return $this->belongsTo(InsuranceScheme::class, 'insurance_scheme_id');
    }
}
