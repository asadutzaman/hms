<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnaesthesiaRecord extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'anaesthesia_records';

    protected $fillable = [
        'organogram_id',
        'ot_booking_id',
        'anaesthetist_id',
        'anaesthesia_type',
        'asa_grade',
        'premedication',
        'induction_agent',
        'airway_management',
        'notes',
        'recovery_notes',
        'started_at',
        'ended_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'               => 'integer',
        'organogram_id'    => 'integer',
        'ot_booking_id'    => 'integer',
        'anaesthetist_id'  => 'integer',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'started_at'       => 'datetime:Y-m-d H:i:s',
        'ended_at'         => 'datetime:Y-m-d H:i:s',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'            => StatusEnum::ACTIVE,
        'anaesthesia_type'  => 'general',
        'sort_order'        => 0,
    ];

    public function otBooking(): BelongsTo
    {
        return $this->belongsTo(OtBooking::class, 'ot_booking_id');
    }

    public function anaesthetist(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'anaesthetist_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AnaesthesiaRecordEntry::class, 'anaesthesia_record_id')->orderBy('recorded_at');
    }
}
