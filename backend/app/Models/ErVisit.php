<?php

namespace App\Models;

use App\Enums\ErVisitStatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErVisit extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'er_visits';

    protected $fillable = [
        'organogram_id',
        'er_visit_no',
        'patient_id',
        'arrival_mode',
        'chief_complaint',
        'arrival_at',
        'er_status',
        'disposition',
        'linked_admission_id',
        'disposed_at',
        'registered_by',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                   => 'integer',
        'organogram_id'        => 'integer',
        'patient_id'           => 'integer',
        'arrival_at'           => 'datetime:Y-m-d H:i:s',
        'linked_admission_id'  => 'integer',
        'disposed_at'          => 'datetime:Y-m-d H:i:s',
        'registered_by'        => 'integer',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'sort_order'           => 'integer',
        'status'               => 'integer',
        'created_at'           => 'datetime:Y-m-d H:i:s',
        'updated_at'           => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'       => 1,
        'sort_order'   => 0,
        'arrival_mode' => 'walk_in',
        'er_status'    => ErVisitStatusEnum::WAITING_TRIAGE,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function linkedAdmission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'linked_admission_id');
    }

    public function triages(): HasMany
    {
        return $this->hasMany(ErTriage::class, 'er_visit_id')->orderByDesc('triaged_at');
    }
}
