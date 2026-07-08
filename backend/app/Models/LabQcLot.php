<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabQcLot extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'lab_qc_lots';

    protected $fillable = [
        'organogram_id',
        'lab_test_parameter_id',
        'lot_number',
        'level',
        'target_mean',
        'target_sd',
        'expiry_date',
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
        'id'                     => 'integer',
        'organogram_id'          => 'integer',
        'lab_test_parameter_id'  => 'integer',
        'target_mean'            => 'decimal:4',
        'target_sd'              => 'decimal:4',
        'expiry_date'            => 'date:Y-m-d',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
        'sort_order'             => 'integer',
        'status'                 => 'integer',
        'created_at'             => 'datetime:Y-m-d H:i:s',
        'updated_at'             => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'level'      => 'Level 1',
        'sort_order' => 0,
    ];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(LabTestParameter::class, 'lab_test_parameter_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(LabQcRun::class, 'qc_lot_id')->orderBy('run_date');
    }
}
