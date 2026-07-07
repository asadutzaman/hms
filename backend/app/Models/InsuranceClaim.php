<?php

namespace App\Models;

use App\Enums\InsuranceClaimStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'insurance_claims';

    protected $fillable = [
        'organogram_id',
        'claim_no',
        'patient_id',
        'insurance_company_id',
        'insurance_scheme_id',
        'pre_authorization_id',
        'policy_number',
        'billable_type',
        'billable_id',
        'claimed_amount',
        'approved_amount',
        'claim_status',
        'submitted_by',
        'submitted_at',
        'settled_at',
        'notes',
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
        'insurance_company_id'  => 'integer',
        'insurance_scheme_id'   => 'integer',
        'pre_authorization_id'  => 'integer',
        'billable_id'           => 'integer',
        'claimed_amount'        => 'decimal:2',
        'approved_amount'       => 'decimal:2',
        'submitted_by'          => 'integer',
        'submitted_at'          => 'datetime:Y-m-d H:i:s',
        'settled_at'            => 'datetime:Y-m-d H:i:s',
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
        'status'       => 1,
        'sort_order'   => 0,
        'claim_status' => InsuranceClaimStatusEnum::DRAFT,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function insuranceScheme(): BelongsTo
    {
        return $this->belongsTo(InsuranceScheme::class, 'insurance_scheme_id');
    }

    public function preAuthorization(): BelongsTo
    {
        return $this->belongsTo(PreAuthorization::class, 'pre_authorization_id');
    }

    /**
     * Not a true polymorphic Eloquent relation (billable_type stores plain
     * 'opd_bill'/'ipd_bill' strings, not FQCNs), resolved manually in the
     * service layer instead — kept here only as a documented placeholder.
     */
    public function opdBill(): BelongsTo
    {
        return $this->belongsTo(OpdBill::class, 'billable_id');
    }

    public function ipdBill(): BelongsTo
    {
        return $this->belongsTo(IpdBill::class, 'billable_id');
    }
}
