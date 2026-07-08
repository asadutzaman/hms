<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnaesthesiaRecordEntry extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'anaesthesia_record_entries';

    protected $fillable = [
        'organogram_id',
        'anaesthesia_record_id',
        'recorded_at',
        'heart_rate',
        'bp_systolic',
        'bp_diastolic',
        'spo2_pct',
        'respiratory_rate',
        'agent_name',
        'agent_dose',
        'fluids_given',
        'remarks',
        'recorded_by',
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
        'anaesthesia_record_id'  => 'integer',
        'heart_rate'             => 'integer',
        'bp_systolic'            => 'integer',
        'bp_diastolic'           => 'integer',
        'spo2_pct'               => 'integer',
        'respiratory_rate'       => 'integer',
        'recorded_by'            => 'integer',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
        'sort_order'             => 'integer',
        'status'                 => 'integer',
        'recorded_at'            => 'datetime:Y-m-d H:i:s',
        'created_at'             => 'datetime:Y-m-d H:i:s',
        'updated_at'             => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'sort_order' => 0,
    ];

    public function anaesthesiaRecord(): BelongsTo
    {
        return $this->belongsTo(AnaesthesiaRecord::class, 'anaesthesia_record_id');
    }
}
