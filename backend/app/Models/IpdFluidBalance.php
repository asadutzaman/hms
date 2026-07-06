<?php

namespace App\Models;

use App\Enums\IpdFluidBalanceTypeEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdFluidBalance extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_fluid_balances';

    protected $fillable = [
        'organogram_id',
        'admission_id',
        'balance_type',
        'category',
        'amount_ml',
        'shift',
        'recorded_at',
        'recorded_by',
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
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'admission_id'  => 'integer',
        'amount_ml'     => 'decimal:2',
        'recorded_at'   => 'datetime:Y-m-d H:i:s',
        'recorded_by'   => 'integer',
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
        'status'       => 1,
        'sort_order'   => 0,
        'balance_type' => IpdFluidBalanceTypeEnum::INTAKE,
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'admission_id');
    }
}
