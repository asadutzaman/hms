<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabQcRun extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'lab_qc_runs';

    protected $fillable = [
        'organogram_id',
        'qc_lot_id',
        'run_date',
        'measured_value',
        'z_score',
        'is_out_of_control',
        'performed_by',
        'remarks',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                 => 'integer',
        'organogram_id'      => 'integer',
        'qc_lot_id'          => 'integer',
        'measured_value'     => 'decimal:4',
        'z_score'            => 'decimal:2',
        'is_out_of_control'  => 'boolean',
        'performed_by'       => 'integer',
        'created_by'         => 'integer',
        'updated_by'         => 'integer',
        'sort_order'         => 'integer',
        'status'             => 'integer',
        'run_date'           => 'datetime:Y-m-d H:i:s',
        'created_at'         => 'datetime:Y-m-d H:i:s',
        'updated_at'         => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'            => StatusEnum::ACTIVE,
        'is_out_of_control' => false,
        'sort_order'        => 0,
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(LabQcLot::class, 'qc_lot_id');
    }
}
