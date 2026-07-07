<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceCompany extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'insurance_companies';

    protected $fillable = [
        'organogram_id',
        'code',
        'name',
        'tpa_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'credit_limit',
        'is_active',
        'description',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'credit_limit'  => 'decimal:2',
        'is_active'     => 'boolean',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'     => 1,
        'sort_order' => 0,
        'tpa_type'   => 'insurer',
        'is_active'  => true,
    ];

    public function schemes(): HasMany
    {
        return $this->hasMany(InsuranceScheme::class, 'insurance_company_id');
    }
}
