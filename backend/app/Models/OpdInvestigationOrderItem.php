<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdInvestigationOrderItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_investigation_order_items';

    protected $fillable = [
        'organogram_id',
        'opd_investigation_order_id',
        'opd_visit_id',
        'lab_test_id',
        'test_code',
        'test_name',
        'department',
        'sample_type',
        'tat_minutes',
        'unit_price',
        'amount',
        'result_status',
        'reported_at',
        'sequence',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'                         => 'integer',
        'organogram_id'              => 'integer',
        'opd_investigation_order_id' => 'integer',
        'opd_visit_id'               => 'integer',
        'lab_test_id'                => 'integer',
        'tat_minutes'                => 'integer',
        'sequence'                   => 'integer',
        'unit_price'                 => 'decimal:2',
        'amount'                     => 'decimal:2',
        'sort_order'                 => 'integer',
        'status'                     => 'integer',
        'reported_at'                => 'datetime:Y-m-d H:i:s',
        'created_at'                 => 'datetime:Y-m-d H:i:s',
        'updated_at'                 => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'        => StatusEnum::ACTIVE,
        'result_status' => 'pending',
        'sort_order'    => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OpdInvestigationOrder::class, 'opd_investigation_order_id');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }
}
