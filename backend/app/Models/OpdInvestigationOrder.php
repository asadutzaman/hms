<?php

namespace App\Models;

use App\Enums\OpdInvestigationOrderStatusEnum;
use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpdInvestigationOrder extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_investigation_orders';

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'patient_id',
        'order_no',
        'priority',
        'ordered_by',
        'ordered_at',
        'clinical_indication',
        'notes',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'opd_visit_id'  => 'integer',
        'patient_id'    => 'integer',
        'ordered_by'    => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'string',
        'ordered_at'    => 'datetime:Y-m-d H:i:s',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => OpdInvestigationOrderStatusEnum::ORDERED,
        'sort_order' => 0,
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function orderer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpdInvestigationOrderItem::class, 'opd_investigation_order_id')->orderBy('sequence');
    }
}
