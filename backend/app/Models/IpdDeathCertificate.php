<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdDeathCertificate extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_death_certificates';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'certificate_no',
        'date_of_death',
        'time_of_death',
        'immediate_cause',
        'antecedent_cause',
        'underlying_cause',
        'other_significant_conditions',
        'manner_of_death',
        'is_finalized',
        'certified_by',
        'certified_at',
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
        'admission_id'  => 'integer',
        'date_of_death' => 'date:Y-m-d',
        'is_finalized'  => 'boolean',
        'certified_by'  => 'integer',
        'certified_at'  => 'datetime:Y-m-d H:i:s',
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
        'status'          => 1,
        'sort_order'      => 0,
        'manner_of_death' => 'natural',
        'is_finalized'    => false,
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'admission_id');
    }
}
