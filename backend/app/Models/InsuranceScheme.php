<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceScheme extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'insurance_schemes';

    protected $fillable = [
        'organogram_id',
        'insurance_company_id',
        'name',
        'coverage_percent',
        'max_limit',
        'covered_services',
        'is_active',
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
        'insurance_company_id'  => 'integer',
        'coverage_percent'      => 'decimal:2',
        'max_limit'             => 'decimal:2',
        'is_active'             => 'boolean',
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
        'status'           => 1,
        'sort_order'       => 0,
        'coverage_percent' => 100,
        'is_active'        => true,
    ];

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }
}
