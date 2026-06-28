<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdVisitAuditLog extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_visit_audit_logs';

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'actor_id',
        'action',
        'from_status',
        'to_status',
        'remarks',
        'payload',
        'occurred_at',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'opd_visit_id'  => 'integer',
        'actor_id'      => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'payload'       => 'array',
        'occurred_at'   => 'datetime:Y-m-d H:i:s',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'sort_order' => 0,
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
