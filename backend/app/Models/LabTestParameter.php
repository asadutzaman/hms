<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTestParameter extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'lab_test_parameters';

    protected $fillable = [
        'organogram_id',
        'lab_test_id',
        'parameter_name',
        'unit',
        'result_data_type',
        'select_options',
        'critical_low',
        'critical_high',
        'sequence',
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
        'lab_test_id'   => 'integer',
        'critical_low'  => 'decimal:4',
        'critical_high' => 'decimal:4',
        'sequence'      => 'integer',
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
        'status'           => 1,
        'sort_order'       => 0,
        'sequence'         => 1,
        'result_data_type' => 'numeric',
    ];

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function referenceRanges(): HasMany
    {
        return $this->hasMany(LabTestReferenceRange::class, 'lab_test_parameter_id');
    }
}
