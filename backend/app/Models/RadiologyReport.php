<?php

namespace App\Models;

use App\Enums\RadReportStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyReport extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'radiology_reports';

    protected $fillable = [
        'organogram_id',
        'radiology_order_item_id',
        'radiology_report_template_id',
        'findings',
        'impression',
        'report_status',
        'reported_by',
        'reported_at',
        'verified_by',
        'verified_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                            => 'integer',
        'organogram_id'                 => 'integer',
        'radiology_order_item_id'       => 'integer',
        'radiology_report_template_id'  => 'integer',
        'reported_by'                   => 'integer',
        'reported_at'                   => 'datetime:Y-m-d H:i:s',
        'verified_by'                   => 'integer',
        'verified_at'                   => 'datetime:Y-m-d H:i:s',
        'created_by'                    => 'integer',
        'updated_by'                    => 'integer',
        'sort_order'                    => 'integer',
        'status'                        => 'integer',
        'created_at'                    => 'datetime:Y-m-d H:i:s',
        'updated_at'                    => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'        => 1,
        'sort_order'    => 0,
        'report_status' => RadReportStatusEnum::DRAFT,
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(RadiologyOrderItem::class, 'radiology_order_item_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RadiologyReportTemplate::class, 'radiology_report_template_id');
    }
}
