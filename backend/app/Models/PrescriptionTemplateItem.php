<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionTemplateItem extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'prescription_template_id',
        'drug_id',
        'drug_name',
        'generic_name',
        'strength',
        'dosage_form',
        'dose_value',
        'dose_unit',
        'frequency',
        'duration_value',
        'duration_unit',
        'route',
        'instruction',
        'is_prn',
        'sequence',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                        => 'integer',
        'created_by'                => 'integer',
        'updated_by'                => 'integer',
        'status'                    => 'integer',
        'prescription_template_id'  => 'integer',
        'drug_id'                   => 'integer',
        'is_prn'                    => 'boolean',
        'sequence'                  => 'integer',
        'dose_value'                => 'decimal:3',
        'duration_value'            => 'integer',
        'created_at'                => 'datetime:Y-m-d H:i:s',
        'updated_at'                => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'is_prn'     => false,
        'frequency'  => 'OD',
        'route'      => 'oral',
        'duration_unit' => 'days',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PrescriptionTemplate::class, 'prescription_template_id');
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }
}
