<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodDonor extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'blood_donors';

    protected $fillable = [
        'organogram_id',
        'donor_no',
        'name',
        'gender',
        'dob',
        'blood_group',
        'phone',
        'address',
        'last_donation_date',
        'total_donations',
        'is_deferred',
        'deferral_reason',
        'deferral_until_date',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                   => 'integer',
        'organogram_id'        => 'integer',
        'total_donations'      => 'integer',
        'is_deferred'          => 'boolean',
        'dob'                  => 'date:Y-m-d',
        'last_donation_date'   => 'date:Y-m-d',
        'deferral_until_date'  => 'date:Y-m-d',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'sort_order'           => 'integer',
        'status'               => 'integer',
        'created_at'           => 'datetime:Y-m-d H:i:s',
        'updated_at'           => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'          => StatusEnum::ACTIVE,
        'total_donations' => 0,
        'is_deferred'     => false,
        'sort_order'      => 0,
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(BloodDonation::class, 'donor_id')->orderByDesc('donation_date');
    }
}
