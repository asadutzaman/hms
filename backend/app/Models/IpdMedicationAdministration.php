<?php

namespace App\Models;

use App\Enums\IpdMedicationAdministrationStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdMedicationAdministration extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'ipd_medication_administrations';

    protected $fillable = [
        'organogram_id',
        'order_id',
        'scheduled_at',
        'administered_at',
        'administration_status',
        'administered_by',
        'witnessed_by',
        'reason',
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
        'id'              => 'integer',
        'organogram_id'   => 'integer',
        'order_id'        => 'integer',
        'scheduled_at'    => 'datetime:Y-m-d H:i:s',
        'administered_at' => 'datetime:Y-m-d H:i:s',
        'administered_by' => 'integer',
        'witnessed_by'    => 'integer',
        'created_by'      => 'integer',
        'updated_by'      => 'integer',
        'sort_order'      => 'integer',
        'status'          => 'integer',
        'created_at'      => 'datetime:Y-m-d H:i:s',
        'updated_at'      => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'                => 1,
        'sort_order'            => 0,
        'administration_status' => IpdMedicationAdministrationStatusEnum::SCHEDULED,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(IpdMedicationOrder::class, 'order_id');
    }
}
