<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;

class RadiologyReportTemplate extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'radiology_report_templates';

    protected $fillable = [
        'organogram_id',
        'name',
        'modality',
        'body_part',
        'findings_template',
        'impression_template',
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
        'id'            => 'integer',
        'organogram_id' => 'integer',
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
        'is_active'  => true,
    ];
}
