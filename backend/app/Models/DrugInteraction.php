<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugInteraction extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'organogram_id',
        'drug_a_id',
        'drug_b_id',
        'severity',
        'description',
        'recommendation',
        'status',
        'sort_order',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'         => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'status'     => 'integer',
        'drug_a_id'  => 'integer',
        'drug_b_id'  => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'severity'   => 'string',
    ];

    protected $attributes = [
        'status'   => StatusEnum::ACTIVE,
        'severity' => 'moderate',
    ];

    public function drugA(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'drug_a_id');
    }

    public function drugB(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'drug_b_id');
    }
}
