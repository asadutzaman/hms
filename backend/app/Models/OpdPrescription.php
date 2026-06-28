<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpdPrescription extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_prescriptions';

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'patient_id',
        'prescribed_by',
        'prescribed_at',
        'advice',
        'follow_up_date',
        'notes',
        'is_printed',
        'printed_at',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'             => 'integer',
        'organogram_id'  => 'integer',
        'opd_visit_id'   => 'integer',
        'patient_id'     => 'integer',
        'prescribed_by'  => 'integer',
        'is_printed'     => 'boolean',
        'sort_order'     => 'integer',
        'status'         => 'integer',
        'prescribed_at'  => 'datetime:Y-m-d H:i:s',
        'follow_up_date' => 'date:Y-m-d',
        'printed_at'     => 'datetime:Y-m-d H:i:s',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'is_printed' => false,
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

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpdPrescriptionItem::class, 'opd_prescription_id')->orderBy('sequence');
    }
}
