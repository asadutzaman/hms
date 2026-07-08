<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodDonation extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'blood_donations';

    protected $fillable = [
        'organogram_id',
        'donation_no',
        'donor_id',
        'donation_date',
        'volume_ml',
        'hemoglobin_g_dl',
        'collected_by',
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
        'id'               => 'integer',
        'organogram_id'    => 'integer',
        'donor_id'         => 'integer',
        'volume_ml'        => 'integer',
        'hemoglobin_g_dl'  => 'decimal:1',
        'collected_by'     => 'integer',
        'donation_date'    => 'date:Y-m-d',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'    => StatusEnum::ACTIVE,
        'volume_ml' => 450,
        'sort_order' => 0,
    ];

    public function donor(): BelongsTo
    {
        return $this->belongsTo(BloodDonor::class, 'donor_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(BloodUnit::class, 'donation_id');
    }
}
