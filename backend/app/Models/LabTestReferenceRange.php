<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabTestReferenceRange extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'lab_test_reference_ranges';

    protected $fillable = [
        'organogram_id',
        'lab_test_parameter_id',
        'gender',
        'age_min_years',
        'age_max_years',
        'range_low',
        'range_high',
        'range_text',
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
        'age_min_years'          => 'integer',
        'age_max_years'          => 'integer',
        'range_low'              => 'decimal:4',
        'range_high'             => 'decimal:4',
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
        'status'        => 1,
        'sort_order'    => 0,
        'gender'        => 'all',
        'age_min_years' => 0,
    ];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(LabTestParameter::class, 'lab_test_parameter_id');
    }
}
